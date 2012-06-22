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
    PHPDOC\Element\ElementInterface
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

        echo $dom->saveXML();
        $this->saveArchive($output);
    }

    /**
     * Create a new WordML document
     *
     * Processes the elements in the Document given and creates a WordML DOM.
     * All relationships, content-types and media files will be added to the
     * archive, as needed. The resulting file is NOT actually added to the ZIP
     * archive. The target is merely used for relationship mapping.
     *
     * @param Document     $document Document to process.
     * @param \DOMDocument $root     DOM object representing WordML document (usually the <body>)
     * @param string       $target   Filename that will be used in the archive
     */
    protected function createDocument($document, $root, $target)
    {
        $total = count($document);
        $idx = 0;
        foreach ($document as $section) {
            $idx += 1;

            // process section body content ...
            foreach ($section as $element) {
                $this->traverseElement($element, $root, 'processElement');
            }

            $dom = $root->ownerDocument;

            // The body can not be blank ...
            if (!$root->hasChildNodes()) {
                $root->appendChild( $dom->createElement('w:p') );
            }

            // Add the section properties <w:sectPr>. It either goes INTO or
            // AFTER the last paragraph depending if we're on the last section.
            $sect = $dom->createElement('w:sectPr');
            $this->formatter->format($section, $sect);

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

    }

    /**
     * Process the element
     */
    private function processElement($element, $root)
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
     * Process a Paragraph.
     *
     * A paragraph <w:p> can contain run level content
     */
    private function processParagraphElement($element, $root)
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
     * Process a Table.
     *
     * A table <w:tbl> can contain rows of block level content.
     */
    private function processTableElement($element, $root)
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
                if ($this->formatter->format($row, $prop)) {
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
    private function processLinkElement($element, $root)
    {
        $dom = $root->ownerDocument;
        $node = $dom->createElement('w:hyperlink');
        $root->appendChild($node);

        $rid = $this->getRelationId('hyperlink', $element->getTarget());
        if (!$rid) {
            $rid = $this->getNextRelationId();
            $this->addRelation($rid, 'hyperlink', $element->getTarget(), $element->getTarget());
        }

        $node->appendChild(new \DOMAttr('w:id', $rid));

        return $node;
    }

    /**
     * Process a TextRun.
     *
     * A TextRun <w:r> can contain text elements.
     */
    private function processTextRunElement($element, $root)
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
     * Process a Text.
     *
     * A Text <w:t> can contain text elements.
     */
    private function processTextElement($element, $root)
    {
        $dom = $root->ownerDocument;
        $node = $dom->createElement('w:t', $element->getContent());
        $root->appendChild($node);
    }

    /**
     * Processes a single media element. Updates the archive with its
     * content-type, relationship and media (image) file.
     *
     * @param ElementInterface $element The element to process.
     */
    private function processImageElement($element, $root)
    {

        // @todo
        $rid = 'rId1';

        $dom = $root->ownerDocument;

        // @todo Change this to use DrawingML instead of VML
        $node = $dom->createElement('w:pict');
        $rect = $dom->createElement('v:rect');
        $rect->appendChild(new \DOMAttr('stroked', 'f'));
        $rect->appendChild(new \DOMAttr('style', sprintf('width:%spx;height:%spx',
                                                          $element->getWidth(),
                                                          $element->getHeight()
        )));

        $fill = $dom->createElement('v:fill');
        $fill->appendChild(new \DOMAttr('r:id', $rid));
        $fill->appendChild(new \DOMAttr('type', 'frame'));

        $root->appendChild($node);
        $node->appendChild($rect);
        $rect->appendChild($fill);

        //$src = $element->getSource();
        //if (!$this->getRelationId('image', $src)) {
        //    $id += 1;
        //    $rid = $this->getNextRelationId();
        //    $target = sprintf('word/media/image%d.%s', $id, $element->getExtension());
        //    if (substr($src, 0, 5) == 'data:') {
        //        // The source is actually a data uri: data:image/png;base64,...
        //        // @todo this blindly assumes the data is base64 encoded
        //        $data = base64_decode(substr($src, strpos($src, ',')+1));
        //        $this->addFileFromString($data, $target);
        //    } else {
        //        $this->addFile($src, $target);
        //    }
        //    $this->addContentType($element->getExtension(), $element->getContentType());
        //    $this->addRelation($rid, 'image', $target, $src);
        //}
    }

    /**
     * Traverse a document element and all of its children
     *
     * @param mixed  $element The element to traverse
     * @param string $method  Optional method name to call before traversing any children
     */
    private function traverseElement($element, $root, $method)
    {
        //if ($method !== null) {
        //    if (!method_exists($this, $method)) {
        //        throw new SaveException("Undefined method specified \"$method\"");
        //    }
        //    $this->$method($element, $root);
        //}
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

    protected function setDocumentNamespaces($root, $namespaces = null)
    {
        // @todo This should probably be a class property so it can be overridden
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
                                 $this->properties->get('tmp_prefix', ''));
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
                } else {
                    @unlink($this->zipFile);
                }
            }
        }
    }

    /**
     * Saves the DOM as a Word "docx" file.
     *
     * @param string   $output   Output filename or other stream.
     */
    //protected function saveDom($output = null)
    //{
    //    if ($output === null) {
    //        $output = 'php://output';
    //    }
    //
    //    $this->processSections();
    //    $this->processPackageRelationships();
    //    $this->processDocumentRelationships();
    //    $this->processContentTypes();
    //    $this->processMainDocument();
    //
    //    return $this;
    //}

    /**
     * Process all sections within the document and process any elements.
     */
    //protected function processSections()
    //{
    //    // Loop through all elements within each section looking for elements
    //    // that require relationships, content-types, media files, etc...
    //    foreach ($this->document->getSections() as $section) {
    //        $this->traverseElement($section, 'processElement');
    //
    //        if ($section->hasHeaders()) {
    //            foreach ($section->getHeaders() as $element) {
    //                $this->traverseElement($element, 'processElement');
    //            }
    //        }
    //
    //        if ($section->hasFooters()) {
    //            foreach ($section->getFooters() as $element) {
    //                $this->traverseElement($element, 'processElement');
    //            }
    //        }
    //    }
    //}

    /**
     * Processes a single link element. Updates the archive with its
     * relationship.
     *
     * This is a callback for traverseElement() and should not be called directly.
     * Non-link type elements are ignored.
     *
     * @param ElementInterface $element The element to process.
     */
    //private function processLinkElement($element)
    //{
    //    if ($element instanceof LinkInterface) {
    //        if (!$this->getRelationId('hyperlink', $element->getTarget())) {
    //            $rid = $this->getNextRelationId();
    //            $this->addRelation($rid, 'hyperlink', $element->getTarget(), $element->getTarget());
    //        }
    //    }
    //}

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
        //print implode("\n", $xml);

        $this->addFileFromString(implode("\n", $xml), '[Content_Types].xml');
    }

    /**
     * Process the main document and add it to the archive
     */
    private function processMainDocument()
    {
        // @todo temporary ...
        $namespaces = array(
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

        if (!$this->wordDom) {
            $this->setDom();
        }

        $w = $this->wordDom;    // alias
        $root = $w->createElement('w:document');
        foreach ($namespaces as $ns => $uri) {
            $root->appendChild(new \DOMAttr($ns, $uri));
        }
        $w->appendChild($root);

        $body = $w->createElement('w:body', '');
        $root->appendChild($body);

        $p = $w->createElement('w:p');
        $r = $w->createElement('w:r');
        $t = $w->createElement('w:t', 'Hello World');
        $r->appendChild($t);
        $p->appendChild($r);
        $body->appendChild($p);

        $xml[] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?' . '>';
        $xml[] = '<w:document xmlns:ve="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml">';
        $xml[] =    '<w:body>';
        $xml[] =        '<w:p>';
        $xml[] =            '<w:r>';
        $xml[] =                '<w:t>Hello World! ' . date('Y-m-d H:i:s') . '</w:t>';
        $xml[] =            '</w:r>';
        $xml[] =        '</w:p>';
        $xml[] =    '</w:body>';
        $xml[] = '</w:document>';

        $this->addFileFromString(implode("\n", $xml), 'word/document.xml');

    }

    /**
     * Add a new relationship
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
            ->addContentType('/docProps/app.xml', 'application/vnd.openxmlformats-officedocument.extended-properties+xml', true)
            ->addContentType('/docProps/core.xml', 'application/vnd.openxmlformats-package.core-properties+xml', true)
            ->addContentType('/word/document.xml', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml', true)
            ;
    }

    // not used
    //private function traverseDOM(\DOMNode $node, $method)
    //{
    //    if ($method !== null) {
    //        //call_user_func(array($this, $method), $node);
    //        $this->$method($node);
    //    }
    //    if ($node->hasChildNodes()) {
    //        foreach ($node->childNodes as $child) {
    //            if ($child->nodeType == XML_ELEMENT_NODE) {
    //                $this->traverseDOM($child, $method);
    //            }
    //        }
    //    }
    //}


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
     * Set a new DOMDocument object that will be used to generate the main
     * WordML document.
     *
     * Creates a new DOMDocument object. The caller can pass in a new object
     * of their own creation, or pass nothing and a default object will be
     * created instead.
     *
     * @param \DOMDocument $dom Optional new DOMDocument object
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
