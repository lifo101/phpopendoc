<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 *
 */
namespace PHPDOC\Document\Writer;

use PHPDOC\Document,
    PHPDOC\Property\Properties,
    PHPDOC\Document\WriterInterface,
    PHPDOC\Document\Writer\Exception\SaveException,
    PHPDOC\Document\Writer\Word2007\Formatter,
    PHPDOC\Element\ElementInterface,
    PHPDOC\Element\Paragraph
    ;

/**
 * Writer\Word2007 will write Microsoft Word 2007 "docx" files
 *
 * This class produces a "Word 2007" "docx" file based on the Document given.
 */
class Word2007 implements WriterInterface
{
    protected $document;
    protected $docDom;
    protected $wordDom;
    protected $properties;
    protected $formatter;

    protected $zip;
    protected $zipFile;

    protected $relId;
    protected $relationships;
    protected $relationshipsMap;
    protected $contentTypes;

    /**
     * Construct a new instance
     *
     * @param Document $document   Optional Document object.
     * @param mixed    $properties Optional properties for document creation.
     */
    public function __construct(Document $document = null, $properties = null)
    {
        $this->document = $document;
        $this->properties = new Properties($properties);
        $this->formatter = new Formatter();

        $this->relId = 0;
        $this->relationships = array();
        $this->relationshipsMap = array();
        $this->addDefaultContentTypes();
    }

    /**
     * Saves the document as a Microsoft Word document "docx" file.
     *
     * @param string   $output   Output filename or other stream.
     * @param Document $document Document to process. Optional only if a
     *                           document was given to the constructor.
     */
    public function save($output = null, Document $document = null)
    {
        if ($output === null) {
            $output = 'php://output';
        }
        if ($document === null) {
            if ($this->document === null) {
                throw new SaveException("No document defined");
            }
            $document = $this->document;
        }

        $this->initArchive();

        // setup DOM for main document
        $dom = $this->setDom();

        // create root
        $root = $dom->createElement('w:document');
        $this->setDocumentNamespaces($root);
        $dom->appendChild($root);

        // create body
        $body = $dom->createElement('w:body');
        $root->appendChild($body);

        // create the main document "Story"
        $this->createDocument($document, $body, 'word/document.xml');
        //echo $dom->saveXML();

        $this->processPackageRelationships();
        $this->processDocumentCoreProperties();
        $this->processDocumentSettings();
        $this->processDocumentRelationships();
        $this->processContentTypes();

        $this->saveArchive($output);
    }

    /**
     * Create a new WordML document
     *
     * Processes the elements in the Document given and creates a WordML DOM.
     * All relationships, content-types and media files will be added to the
     * physical archive, as needed.
     *
     * @param Document $document Document to process.
     * @param \DOMNode $root     DOM node representing WordML document (usually the <body>)
     * @param string   $target   Filename that will be used in the archive
     */
    protected function createDocument($document, \DOMNode $root, $target)
    {
        $dom = $root->ownerDocument;
        $total = count($document);
        $idx = 0;
        foreach ($document as $section) {
            $idx += 1;

            // The section can not be blank
            // @todo This creates a side-effect as it causes a blank paragraph
            //       to be added to the section object.
            if (!$section->hasElements()) {
                $section[] = new Paragraph();
            }

            // process section body content
            foreach ($section as $element) {
                $this->traverseElement($element, $root, 'processElement');
            }

            // create section properties
            $sect = $dom->createElement('w:sectPr');
            $this->formatter->format($section, $sect);

            // Add the section properties. It either goes INTO or AFTER the last
            // paragraph depending if we're on the last section.
            if ($idx == $total) {               // last section of document
                $root->appendChild($sect);

            } else {                            // last element of section
                $node = $root->lastChild;
                // there must be a paragraph at the end to add the sectPr
                if ($node->nodeName != 'w:p') {
                    $node = $dom->createElement('w:p');
                    $root->appendChild();
                }

                // the fist node must be an <w:pPr> element
                if (!$node->firstChild or $node->firstChild->nodeName != 'w:pPr') {
                    // insert a new <w:pPr> node at the beginning
                    if ($node->firstChild) {
                        $node = $node->insertBefore($dom->createElement('w:pPr'), $node->firstChild);
                    } else {
                        $ppr = $dom->createElement('w:pPr');
                        $node->appendChild($ppr);
                        $node = $ppr;
                    }
                } else {
                    $node = $node->firstChild;  // point to <w:pPr>
                }

                $node->appendChild($sect);
            }
        }

        $this->addFileFromString($dom->saveXML(), $target);
    }

    /**
     * Factory method that processes any element and passes it along to the
     * appropriate method that will actually handle the processing.
     *
     * @param ElementInterface $element Element to process
     * @param \DOMNode         $root    The root node to update
     * @return \DOMNode Returns the new or original root node
     */
    private function processElement(ElementInterface $element, \DOMNode $root)
    {
        $interface = $element->getInterface();
        $method = str_replace('Interface', 'Element', $interface);
        if (($pos = strrpos($method, '\\')) !== false) {
            $method = substr($method, $pos+1);
        }
        $method = 'process' . $method;
        if (method_exists($this, $method)) {
            return $this->$method($element, $root);
        } else {
            // @todo should an Exception be thrown for unknown elements?
            //throw new SaveException("Unable to process unknown element \"" . get_class($element) . "\"");
        }

        return $root;
    }

    /**
     * Process a Paragraph
     */
    private function processParagraphElement(ElementInterface $element, \DOMNode $root)
    {
        $dom = $root->ownerDocument;
        $node = $dom->createElement('w:p');
        $root->appendChild($node);

        // add properties for the element
        $prop = $dom->createElement('w:pPr');
        if ($this->formatter->format($element, $prop)) {
            $node->appendChild($prop);
        }

        return $node;
    }

    /**
     * Process a Table
     */
    private function processTableElement(ElementInterface $element, \DOMNode $root)
    {
        $dom = $root->ownerDocument;
        $node = $dom->createElement('w:tbl');
        $root->appendChild($node);

        // add properties for the table
        $prop = $dom->createElement('w:tblPr');
        if ($this->formatter->format($element, $prop)) {
            $node->appendChild($prop);
        }

        // add grid
        $grid = $element->getGrid();
        if ($grid) {
            $tblGrid = $dom->createElement('w:tblGrid');
            $node->appendChild($tblGrid);
            foreach ($grid as $w) {
                $col = $dom->createElement('w:gridCol');
                $col->appendChild(new \DOMAttr('w:w', $w));
                $tblGrid->appendChild($col);
            }
        }

        // add rows
        foreach ($element->getRows() as $row) {
            // skip any rows that have no cells
            if (!$row->hasElements()) {
                continue;
            }

            $tr = $dom->createElement('w:tr');
            $node->appendChild($tr);

            // add properties for the row
            $prop = $dom->createElement('w:trPr');
            if ($this->formatter->format($row, $prop)) {
                $tr->appendChild($prop);
            }

            // add cells for the current row
            foreach ($row->getElements() as $cell) {
                $tc = $dom->createElement('w:tc');
                $tr->appendChild($tc);

                // add properties for the cell
                $prop = $dom->createElement('w:tcPr');
                if ($this->formatter->format($cell, $prop)) {
                    $tc->appendChild($prop);
                }

                // add all child elements to the cell
                foreach ($cell->getElements() as $element) {
                    $this->traverseElement($element, $tc, 'processElement');
                }
            }
        }

        return $node;
    }

    /**
     * Process a Link
     *
     * A hyperlink <w:hyperlink> can contain run level content
     */
    private function processLinkElement(ElementInterface $element, \DOMNode $root)
    {
        $dom = $root->ownerDocument;
        $node = $dom->createElement('w:hyperlink');
        $root->appendChild($node);

        $rid = $this->getRelationId('hyperlink', $element->getTarget());
        if (!$rid) {
            $rid = $this->getNextRelationId();
            $this->addRelation($rid, 'hyperlink', $element->getTarget(), $element->getTarget(), 'External');
        }

        $node->appendChild(new \DOMAttr('r:id', $rid));

        return $node;
    }

    /**
     * Process a TextRun.
     *
     * A TextRun <w:r> can contain text elements.
     */
    private function processTextRunElement(ElementInterface $element, \DOMNode $root)
    {
        $dom = $root->ownerDocument;
        $node = $dom->createElement('w:r');
        $root->appendChild($node);

        // add properties for the element
        $prop = $dom->createElement('w:rPr');
        if ($this->formatter->format($element, $prop)) {
            $node->appendChild($prop);
        }

        return $node;
    }

    /**
     * Process a Text element
     */
    private function processTextElement(ElementInterface $element, \DOMNode $root)
    {
        $dom = $root->ownerDocument;
        $node = $dom->createElement('w:t', $element->getContent());
        $root->appendChild($node);
        return $root;
    }

    /**
     * Processes a single media element. Updates the archive with its
     * content-type, relationship and media (image) file.
     */
    private function processImageElement(ElementInterface $element, \DOMNode $root)
    {
        static $imgId = 0;

        $src = $element->getSource();
        if (!$element->isFile()) {
            // sha1 the raw data:uri
            $src = sha1($src);
        }

        $rid = $this->getRelationId('image', $src);
        if (!$rid) {
            $imgId += 1;
            $rid = $this->getNextRelationId();
            $target = sprintf('%s/image%d.%s',
                              $this->properties->get('media_path', 'word/media'),
                              $imgId,
                              $element->getExtension()
            );
            // make sure there's no leading slash
            $target = ltrim($target, '/');

            if ($element->isFile()) {
                $this->addFile($src, $target);
            } else {
                $this->addFileFromString($element->getData(), $target);
            }
            $this->addContentType($element->getExtension(), $element->getContentType());
            $this->addRelation($rid, 'image', '/' . $target, $src);
        }

        $dom = $root->ownerDocument;

        // @todo Change this to use DrawingML instead of VML
        $node = $dom->createElement('w:pict');
        $rect = $dom->createElement('v:shape');
        $rect->appendChild(new \DOMAttr('type', '#_x0000_t75'));
        $rect->appendChild(new \DOMAttr('style', sprintf('width:%dpx;height:%dpx',
            $element->getWidth(),
            $element->getHeight()
        )));

        $fill = $dom->createElement('v:imagedata');
        $fill->appendChild(new \DOMAttr('r:id', $rid));
        $fill->appendChild(new \DOMAttr('o:title', ''));

        $root->appendChild($node);
        $node->appendChild($rect);
        $rect->appendChild($fill);

        return $root;
    }

    /**
     * Traverse a document element and all of its children
     *
     * This is the main method used to travese the document elements to create
     * the XML tree required for saving the WordML.
     *
     * @param mixed  $element The element to traverse
     * @param string $method  Optional method name to call before traversing any children
     */
    private function traverseElement(ElementInterface $element, \DOMNode $root, $method)
    {
        $newRoot = $this->$method($element, $root);
        if ($newRoot !== null) {
            $root = $newRoot;
        }
        if ($element->hasElements()) {
            foreach ($element->getElements() as $child) {
                $this->traverseElement($child, $root, $method);
            }
        }
    }

    protected function setDocumentNamespaces(\DOMNode $root, $namespaces = null)
    {
        static $default = array(
            'xmlns:ve'  => 'http://schemas.openxmlformats.org/markup-compatibility/2006',
            'xmlns:o'   => 'urn:schemas-microsoft-com:office:office',
            'xmlns:r'   => 'http://schemas.openxmlformats.org/officeDocument/2006/relationships',
            'xmlns:m'   => 'http://schemas.openxmlformats.org/officeDocument/2006/math',
            'xmlns:v'   => 'urn:schemas-microsoft-com:vml',
            'xmlns:wp'  => 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing',
            'xmlns:w10' => 'urn:schemas-microsoft-com:office:word',
            'xmlns:w'   => 'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
            'xmlns:wne' => 'http://schemas.microsoft.com/office/word/2006/wordml',
        );

        if ($namespaces === null) {
            $namespaces = array();
        }

        foreach ($namespaces + $default as $ns => $uri) {
            $root->appendChild(new \DOMAttr($ns, $uri));
        }

        return $this;
    }

    /**
     * Initialize the document archive (zip)
     *
     * @throws SaveException
     */
    protected function initArchive()
    {
        $this->zipFile = tempnam($this->properties->get('tmp_path', sys_get_temp_dir()),
                                 $this->properties->get('tmp_prefix', 'phpopendoc_'));
        $this->zip = new \ZipArchive();

        if ($this->zip->open($this->zipFile, \ZipArchive::OVERWRITE) !== true) {
            throw new SaveException("Error creating temporary file \"$this->zipFile\" for writing");
        }
    }

    /**
     * Save the archive to disk (or wherever the output points to)
     *
     * @throws SaveException
     * @param string   $output   Output filename or other stream.
     */
    protected function saveArchive($output)
    {
        if ($this->zip) {
            if (!$this->zip->close()) {
                throw new SaveException("Error closing temporary document file \"$this->zipFile\"");
            }

            if (!@rename($this->zipFile, $output)) {
                if (!@copy($this->zipFile, $output)) {
                    $err = error_get_last();
                    throw new SaveException("Error saving document to \"$output\". Reason: " . $err['message']);
                }
            }
            @unlink($this->zipFile);
        }
    }

    /**
     * Process all package relationships and add them to the archive
     */
    private function processPackageRelationships()
    {
        // no need to waste memory and use DOMDocument here
        $xml = array();
        $xml[] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml[] = '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $xml[] = '  <Relationship Id="' . $this->getNextRelationId() . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml" />';
        $xml[] = '</Relationships>';

        $this->addFileFromString(implode("\n", $xml), '_rels/.rels');
    }

    private function processDocumentCoreProperties()
    {
        $xml = array();
        $xml[] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml[] = '<coreProperties'
            . 'xmlns="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"'
            . 'xmlns:dcterms="http://purl.org/dc/terms/"'
            . 'xmlns:dc="http://purl.org/dc/elements/1.1/"'
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';

        $xml[] = '</coreProperties>';

        $this->addFileFromString(implode("\n", $xml), 'docProps/core.xml');
    }

    private function processDocumentSettings()
    {
        //// @todo Move static directory somewhere else more appropriate
        //$source = $this->properties->get('static_path', __DIR__ . '/../../../../../static') . '/settings.xml';
        //
        //$this->addContentType('/word/settings.xml', 'application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml', true);
        //$this->addRelation($this->getNextRelationId(), 'settings', 'settings.xml');
        //$this->addFile($source, 'word/settings.xml');
    }

    /**
     * Process all Document relationships and add them to the archive
     */
    private function processDocumentRelationships()
    {
        // no need to waste memory and use DOMDocument here
        $xml = array();
        $xml[] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml[] = '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        foreach ($this->relationships as $rel) {
            $node = sprintf('  <Relationship Id="%s" Target="%s" '
                            . 'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/%s"',
                $rel['Id'],
                $rel['Target'],
                $rel['Type']
            );
            if (isset($rel['TargetMode'])) {
                $node .= sprintf(' TargetMode="%s"', $rel['TargetMode']);
            }
            $node .= "/>";
            $xml[] = $node;
        }
        $xml[] = '</Relationships>';

        $this->addFileFromString(implode("\n", $xml), 'word/_rels/document.xml.rels');
    }

    /**
     * Process all content-types and add it to the archive
     */
    private function processContentTypes()
    {
        // no need to waste memory and use DOMDocument here
        $xml = array();
        $xml[] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml[] = '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';

        foreach ($this->contentTypes as $ext => $ct) {
            if ($ct['override']) {
                $xml[] = sprintf('  <Override PartName="%s" ContentType="%s"/>', $ext, $ct['type']);
            } else {
                $xml[] = sprintf('  <Default Extension="%s" ContentType="%s"/>', $ext, $ct['type']);
            }
        }

        $xml[] = '</Types>';

        $this->addFileFromString(implode("\n", $xml), '[Content_Types].xml');
    }

    /**
     * Add a new relationship.
     *
     * Note: Document relationships are relative to the _rels path.
     *
     * $param string $rid    The Relationship ID
     * @param string $type   The type of relationship (eg: 'image', 'hyperlink', ...)
     * @param string $target The target of the relationship
     * @param string $source The common source (used to map identical resources together)
     * @param string $mode   The optional mode of the target
     */
    private function addRelation($rid, $type, $target, $source = null, $mode = null)
    {
        $this->relationships[$rid] = array(
            'Id' => $rid,
            'Type' => $type,
            'Target' => $target,
            'TargetMode' => $mode
        );
        if ($source !== null) {
            $this->relationshipsMap[$type][$source] = $rid;
        }
        return $this;
    }

    private function getRelationId($type, $source)
    {
        if (isset($this->relationshipsMap[$type][$source])) {
            return $this->relationshipsMap[$type][$source];
        }
        return null;
    }

    /**
     * Add a content-type.
     *
     * Adding the same content-type repeatedly will simply overwrite the
     * previously set value.
     *
     * @param string $extension The file extension or partName.
     * @param string $type The mime-type to associate with the extension.
     * @param boolean $override If true an <Override> content-type is created;
     *                          by default a <Default> content-type is created.
     */
    private function addContentType($extension, $type, $override = false)
    {
        if (!empty($extension)) {
            $this->contentTypes[$extension] = array( 'override' => $override, 'type' => $type );
        } else {
            // @todo should an exception be thrown on empty content-types?
            //throw new SaveException("Empty Content-Type is not allowed");
        }
        return $this;
    }


    /**
     * Add the default content-type's that are common to any document
     */
    private function addDefaultContentTypes()
    {
        return $this
            ->addContentType('xml', 'application/xml')
            ->addContentType('rels', 'application/vnd.openxmlformats-package.relationships+xml')
            ->addContentType('/word/document.xml', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml', true)
            ->addContentType('/wordProps/core.xml', 'http://schemas.openxmlformats.org/package/2006/metadata/core-properties', true)
            ->addContentType('/wordProps/app.xml', 'application/vnd.openxmlformats-officedocument.extended-properties+xml', true)
            ;
    }

    /**
     * Return the next available Relationship ("rId")
     *
     * @param string $prefix String prefix for the returned ID
     */
    protected function getNextRelationId($prefix = 'rId')
    {
        $this->relId += 1;
        return $prefix . $this->relId;
    }

    /**
     * Add a file to the archive
     *
     * @throws SaveException
     * @param string $source Phyiscal path to the file to add
     * @param string $target Logical path to save the file in the archive
     */
    public function addFile($source, $target)
    {
        if (!$this->zip->addFile($source, $target)) {
            throw new SaveException("Error adding file \"$source\" to archive");
        }
    }

    /**
     * Add a file to the archive using the source string as the content
     *
     * @throws SaveException
     * @param string $source String contents
     * @param string $target Logical path to save the file in the archive
     */
    public function addFileFromString($source, $target)
    {
        if (!$this->zip->addFromString($target, $source)) {
            throw new SaveException("Error adding string as file \"$target\" to archive");
        }
    }

    /**
     * Set a new DOMDocument object that will be used to generate a WordML
     * document.
     *
     * Creates a new DOMDocument object. The caller can pass in a new object
     * of their own creation, or pass nothing and a default object will be
     * created instead.
     *
     * @param \DOMDocument $dom Optional new DOMDocument object
     * @return \DOMDocument New DOM document object or the same object passed in
     */
    public function setDom(\DOMDocument $dom = null)
    {
        if ($dom === null) {
            $dom = new \DOMDocument('1.0', 'utf-8');
            $dom->xmlStandalone = true;
            $dom->formatOutput = true;
        }
        // setDom may be called multiple times to process different parts of
        // the document (once for the main doc, headers and footers) so we
        // clone the object to get a unique instance.
        $this->wordDom = clone $dom;
        return $dom;
    }

    /**
     * Returns the current DOM object.
     */
    public function getDom()
    {
        return $this->dom;
    }

    /**
     * Shortcut so caller doesn't have to instantiate a new object to
     * save the document.
     *
     * @throws SaveException
     * @param Document $document The document to save
     * @param string   $output   The output filename or stream
     */
    public static function saveDocument(Document $document, $output = null)
    {
        $writer = new Word2007();
        return $writer->save($output, $document);
    }
}
