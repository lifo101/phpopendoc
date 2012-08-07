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
    PHPDOC\Document\Writer\Word2007\Formatter\StyleFormatter,
    PHPDOC\Style\StyleInterface,
    PHPDOC\Style\ParagraphStyle,
    PHPDOC\Element\ElementException,
    PHPDOC\Element\ElementInterface,
    PHPDOC\Element\HeaderFooterInterface,
    PHPDOC\Element\Paragraph,
    PHPDOC\Element\TextRun,
    PHPDOC\Element\Table
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
    protected $docFile;

    protected $relationships;
    protected $relationshipsMap;
    protected $contentTypes;

    protected $unlink;

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

        $this->relationships = array();
        $this->relationshipsMap = array();
        $this->unlink = array();
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

        // create root
        $dom = $this->setDom();
        $root = $dom->createElement('w:document');
        $this->setDocumentNamespaces($root);
        $dom->appendChild($root);

        // create body
        $body = $dom->createElement('w:body');
        $root->appendChild($body);

        // create start package relationships
        $this->docFile = '';
        $this->addRelationship('officeDocument', 'word/document.xml');
        $this->processDocumentSettings($document);
        $this->processDocumentCoreProperties($document);

        // create the main document
        $this->docFile = 'word/document.xml';
        // create styles before the document so we can determine if a style is
        // valid before it's used.
        $this->processDocumentStyles($document);
        $this->createDocument($document, $body);

        // process package parts
        $this->processRelationships();
        $this->addDefaultContentTypes();
        $this->processContentTypes();

        $this->saveArchive($output);

        $this->cleanup();
    }

    /**
     * Create a header/footer child document
     *
     * @param HeaderFooterInterface $section The document section to traverse
     * @param \DOMNode              $node    The DOM node to append the hdr/ftr
     *                                       reference to.
     */
    protected function createHeaderFooter(HeaderFooterInterface $section, \DOMNode $node, $target)
    {
        static $idx = array();

        $position = $section->getPosition();

        // create root
        $dom = $this->setDom();
        $root = $dom->createElement('w:' . ($position == 'header' ? 'hdr' : 'ftr'));
        $this->setDocumentNamespaces($root);
        $dom->appendChild($root);

        $p = $position . '-' . $section->getType();
        if (!isset($idx[$p])) {
            $idx[$p] = 0;
        }
        $idx[$p] += 1;

        $xmlFile = 'word/' . $position . $idx[$p] . '.xml';
        $rid = $this->addRelationship($position, $xmlFile);

        $tmpFile = $this->docFile;
        $this->docFile = $xmlFile;
        $this->createDocument($section, $root);
        $this->addContentType('/' . $xmlFile,
                              'application/vnd.openxmlformats-officedocument.wordprocessingml.' . $position . '+xml',
                              true);

        $this->docFile = $tmpFile;

        $prop = $node->ownerDocument->createElement('w:' . $position . 'Reference');
        $prop->appendChild(new \DOMAttr('r:id', $rid));
        $prop->appendChild(new \DOMAttr('w:type', $section->getType()));
        $node->appendChild($prop);
    }

    /**
     * Create a new WordML document
     *
     * Processes the elements in the Document given and creates a WordML DOM.
     * All relationships, content-types and media files will be added to the
     * physical archive, as needed.
     *
     * @param mixed    $document Document or Section to process.
     * @param \DOMNode $root     DOM node representing WordML document (usually the <body>)
     * @param string   $target   Filename that will be used in the archive
     */
    protected function createDocument($document, \DOMNode $root, $target = null)
    {
        $dom = $root->ownerDocument;
        $total = count($document);
        $idx = 0;

        // default to the current document being created
        if ($target === null) {
            $target = $this->docFile;
        }

        if ($document instanceof Document) {
            $sections = $document->getSections();
        } else {
            // The $document is actually a Section (technically a HeaderFooterInterface)
            $sections = array( $document );
        }
        foreach ($sections as $section) {
            $idx += 1;

            // process section body content
            if ($section->hasElements()) {
                foreach ($section as $element) {
                    $this->traverseElement($element, $root, 'processElement');
                }
            } else {
                // The body can not be blank
                if (!$section->hasElements()) {
                    $section[] = new Paragraph();
                }
            }

            // create section properties
            $sect = $dom->createElement('w:sectPr');
            $this->formatter->format($section, $sect);

            if (!($section instanceof HeaderFooterInterface)) {
                // process any headers and footers
                $list = array_merge($section->getHeaders(), $section->getFooters());
                foreach ($list as $hf) {
                    $this->createHeaderFooter($hf, $sect, $target);
                }

                // Add the section properties. It either goes INTO or AFTER the
                // last paragraph depending if we're on the last section.
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
     * Process a bookmark mark
     *
     * A bookmark <w:bookMarkStart> and <w:bookMarkEnd> does not contain any
     * children.
     */
    private function processBookmarkMarkElement(ElementInterface $element, \DOMNode $root)
    {
        $dom = $root->ownerDocument;

        $node = $dom->createElement('w:bookmark' . ucfirst($element->getMark()));
        $node->appendChild(new \DOMAttr('w:id', $element->getId()));
        if ($element->getMark() == 'start') {
            $node->appendChild(new \DOMAttr('w:name', $element->getName()));
        }

        $root->appendChild($node);
        //return $root;
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

        // add properties for the element
        $prop = $dom->createElement('w:rPr');
        if ($this->formatter->format($element, $prop)) {
            $node->appendChild($prop);
        }

        $rid = $this->getRelationshipId('hyperlink', $element->getTarget());
        if (!$rid) {
            $rid = $this->addRelationship('hyperlink', $element->getTarget(), $element->getTarget(), true);
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
        // if any whitespace is seen at the begin/end then preserve it
        if (substr($element->getContent(), 0, 1) == ' ' or
            substr($element->getContent(), -1) == ' ') {
            $node->appendChild(new \DOMAttr('xml:space', 'preserve'));
        }
        $root->appendChild($node);
        return $root;
    }

    /**
     * Process a <w:cr/> element
     */
    private function processCrElement(ElementInterface $element, \DOMNode $root)
    {
        $node = $root->ownerDocument->createElement('w:cr');
        $root->appendChild($node);
        return $root;
    }

    /**
     * Process a <w:br/> element
     */
    private function processBrElement(ElementInterface $element, \DOMNode $root)
    {
        $node = $root->ownerDocument->createElement('w:br');
        if ($element->getType() !== null and $element->getType() !== 'textWrapping') {
            $node->appendChild(new \DOMAttr('w:type', $element->getType()));
        }
        if ($element->getClear() !== null and $element->getClear() !== 'none') {
            $node->appendChild(new \DOMAttr('w:clear', $element->getClear()));
        }
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

        $rid = $this->getRelationshipId('image', $src);
        if (!$rid) {
            $imgId += 1;
            $target = sprintf('%s/image%d.%s',
                              trim($this->properties->get('media_path', 'word/media'), '/'),
                              $imgId,
                              $element->getExtension()
            );

            if ($element->isFile()) {
                // remote files must be saved locally before we can add it to
                // the ZIP archive.
                if ($element->isRemoteFile()) {
                    $tmp = tempnam($this->properties->get('tmp_path', sys_get_temp_dir()),
                                   $this->properties->get('tmp_prefix', 'phpopendoc_img_'));
                    try {
                        $element->save($tmp);
                    } catch (ElementException $e) {
                        throw new SaveException($e->getMessage());
                    }
                    $this->addFile($tmp, $target);

                    // can't unlink the file until after the ZIP is closed
                    $this->unlink[]= $tmp;
                } else {
                    $this->addFile($src, $target);
                }
            } else {
                $this->addFileFromString($element->getData(), $target);
            }
            $this->addContentType($element->getExtension(), $element->getContentType());
            $rid = $this->addRelationship('image', $target, $src);
        }

        $dom = $root->ownerDocument;

        // @todo Change this to use DrawingML instead of VML
        $node = $dom->createElement('w:pict');
        $rect = $dom->createElement('v:shape');
        $rect->appendChild(new \DOMAttr('type', '#_x0000_t75'));
        $rect->appendChild(new \DOMAttr('style', sprintf('width:%dpx;height:%dpx',
            $element->getWidth(true),
            $element->getHeight(true)
        )));

        $fill = $dom->createElement('v:imagedata');
        $fill->appendChild(new \DOMAttr('r:id', $rid));
        $fill->appendChild(new \DOMAttr('o:title', $element->getProperties()->get('title', '')));

        $root->appendChild($node);
        $node->appendChild($rect);
        $rect->appendChild($fill);

        return $root;
    }

    private function processTextStyle(StyleInterface $style, \DOMNode $root)
    {
        $dom = $root->ownerDocument;

        $root->appendChild(new \DOMAttr('w:type', 'character'));

        // add general properties
        $element = new Paragraph(null, $style->getProperties());
        $sf = new StyleFormatter();
        $sf->format($element, $root);

        // add run properties (for paragraph mark)
        $element = new TextRun(null, $style->getProperties());
        $prop = $dom->createElement('w:rPr');
        if ($this->formatter->format($element, $prop)) {
            $root->appendChild($prop);
        }

        return $root;

    }

    private function processParagraphStyle(StyleInterface $style, \DOMNode $root)
    {
        $dom = $root->ownerDocument;

        $root->appendChild(new \DOMAttr('w:type', 'paragraph'));

        // add general properties
        $element = new Paragraph(null, $style->getProperties());
        $sf = new StyleFormatter();
        $sf->format($element, $root);

        // add paragraph properties
        $prop = $dom->createElement('w:pPr');
        if ($this->formatter->format($element, $prop)) {
            $root->appendChild($prop);
        }

        // add run properties (for paragraph mark)
        $element = new TextRun(null, $style->getProperties());
        $prop = $dom->createElement('w:rPr');
        if ($this->formatter->format($element, $prop)) {
            $root->appendChild($prop);
        }

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
                $this->unlink[] = $this->zipFile;
            }
        }
    }

    private function processDocumentCoreProperties(Document $document)
    {
        static $tags = array(
            'category'              => 'cp',
            'contentStatus'         => 'cp',
            'contentType'           => 'cp',
            'created'               => 'dcterms',
            'creator'               => 'dc',
            'description'           => 'dc',
            'identifier'            => 'dc',
            'keywords'              => 'cp',
            'language'              => 'dc',
            'lastModifiedBy'        => 'cp',
            'lastPrinted'           => 'cp',
            'modified'              => 'dcterms',
            'revision'              => 'cp',
            'subject'               => 'dc',
            'title'                 => 'dc',
            'version'               => 'cp',
        );

        $props = $document->getProperties();
        $core = isset($props['core']) ? $props['core'] : array();

        // automatically set dates if not defined ...
        if (!isset($core['created'])) {
            $core['created'] = new \DateTime();
        }
        //if (!isset($core['modified'])) {
        //    $core['modified'] = new \DateTime();
        //}

        $xml = array();
        $xml[] = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';
        $xml[] = '<cp:coreProperties'
                   . ' xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"'
                   . ' xmlns:dc="http://purl.org/dc/elements/1.1/"'
                   . ' xmlns:dcterms="http://purl.org/dc/terms/"'
                   . ' xmlns:dcmitype="http://purl.org/dc/dcmitype/"'
                   . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
                   . '>'
        ;

        foreach ($core as $k => $v) {
            if (!isset($tags[$k])) {
                // @todo emit warning or throw Exception for unknown properties?
                continue;
            }

            $tag = $tags[$k] . ':' . $k;

            $attr = '';
            if ($tags[$k] == 'dcterms') {
                $attr = ' xsi:type="dcterms:W3CDTF"';

                if (!($v instanceof \DateTime)) {
                    try {
                        $v = new \DateTime($v);
                    } catch (\Exception $e) {
                        throw new SaveException("Invalid timestamp format for core property $k: \"$v\"");
                    }
                }
            }

            if ($v instanceof \DateTime) {
                $value = $v->format(DATE_W3C);
            } else {
                $value = htmlentities($v, ENT_NOQUOTES, 'utf-8');
            }

            $xml[] = sprintf('  <%s%s>%s</%s>', $tag, $attr, $value, $tag);
        }

        $xml[] = '</cp:coreProperties>';

        // The type of this relationship has a different uri than the others
        // so we have to use the full path here.
        $this->addRelationship('http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties',
                               'docProps/core.xml');
        $this->addFileFromString(implode("\n", $xml), 'docProps/core.xml');
    }

    private function processDocumentStyles(Document $document)
    {
        static $namespaces = array(
            'xmlns:r'   => 'http://schemas.openxmlformats.org/officeDocument/2006/relationships',
            'xmlns:w'   => 'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
        );

        // styles are more complex so using DOMDocument here is warranted
        $dom = $this->setDom();
        $root = $dom->createElement('w:styles');
        $dom->appendChild($root);

        foreach ($namespaces as $ns => $uri) {
            $root->appendChild(new \DOMAttr($ns, $uri));
        }

        // add default styles
        $docDefaults = $dom->createElement('w:docDefaults');
        $root->appendChild($docDefaults);

        $defaults = array(
            'paragraph' => $dom->createElement('w:pPrDefault'),
            'text'      => $dom->createElement('w:rPrDefault')
        );
        foreach ($document->getDefaultStyles() as $s) {
            $type = $s->getType();
            if (isset($defaults[$type])) {
                // create a new node <w:rPr> or <w:pPr>
                $defaultRoot = $dom->createElement( substr($defaults[$type]->nodeName, 0, 5) );
                $docDefaults->appendChild($defaults[$type]);
                $defaults[$type]->appendChild($defaultRoot);

                // @todo The formatter might need to be refactored to NOT
                // require an ElementInterface instance.
                // a temporary element must be created in order to apply the
                // proper formatting. If $type if 'text' it needs to be a 'TextRun'.
                if ($type == 'text') {
                    $type .= 'Run';
                }
                $class = 'PHPDOC\\Element\\' . ucfirst($type);  // PHPDOC\Element\Paragraph or PHPDOC\Element\TextRun
                $element = new $class(null, $s->getProperties());
                $this->formatter->format($element, $defaultRoot);
            }
        }

        if (!$document->getStyle('Normal')) {
            $document[] = new ParagraphStyle('Normal');
        }
        // add remaining styles
        foreach ($document->getStyles() as $style) {
            $type = $style->getType();
            $method = 'process' . ucfirst($type) . 'Style';
            if (method_exists($this, $method)) {
                $node = $dom->createElement('w:style');
                $node->appendChild(new \DOMAttr('w:styleId', $style->getId()));

                $name = $dom->createElement('w:name');
                $name->appendChild(new \DOMAttr('w:val', $style->getName()));
                $node->appendChild($name);

                if ($this->$method($style, $node)) {
                    $root->appendChild($node);
                }
            }
        }

        $this->addContentType('/word/styles.xml', 'application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml', true);
        $this->addRelationship('styles', 'word/styles.xml');
        $this->addFileFromString($dom->saveXML(), 'word/styles.xml');
    }

    private function processDocumentSettings(Document $document)
    {
        // @todo Move static directory somewhere else more appropriate
        $source = $this->properties->get('static_path', __DIR__ . '/../../../../../static') . '/settings.xml';
        if (@file_exists($source)) {
            $this->addContentType('/word/settings.xml', 'application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml', true);
            $this->addRelationship('settings', 'settings.xml');
            $this->addFile($source, 'word/settings.xml');
        } else {
            // @todo Emit warning or throw Exception?
        }
    }

    /**
     * Process all relationships for the Package and Document.
     */
    private function processRelationships()
    {
        foreach ($this->relationships as $file => $rel) {
            if (!$rel['rels']) {
                // do nothing if there's no relationships defined
                continue;
            }

            $xml = array();
            $xml[] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
            $xml[] = '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
            foreach ($rel['rels'] as $r) {
                $root = strpos($r['Type'], '://') === false ? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/' : '';
                $node = sprintf('  <Relationship Id="%s" Target="%s" '
                                . 'Type="%s%s"',
                                $r['Id'],
                                $r['Target'],
                                $root,
                                $r['Type']
                );
                if (isset($r['TargetMode'])) {
                    $node .= sprintf(' TargetMode="%s"', $r['TargetMode']);
                }
                $node .= "/>";
                $xml[] = $node;
            }
            $xml[] = '</Relationships>';

            $this->addFileFromString(implode("\n", $xml), $rel['file']);
        }
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

    private function startRelationshipsPart($file)
    {
        $pi = pathinfo($file);
        $dirname = isset($pi['dirname']) ? '/' . ltrim($pi['dirname'], '/') : '';
        $this->relationships[$file] = array(
            'file' => ltrim(sprintf('%s/_rels/%s.rels', $dirname, $pi['basename']), '/'),
            'root' => $dirname,
            'rid'  => 0,
            'rels' => array()
        );
    }

    /**
     * Add a new relationship.
     *
     * Note: Document relationships are relative to the current docFile
     *
     * @param string $type     The type of relationship (eg: 'image', 'hyperlink', ...)
     * @param string $target   The target of the relationship
     * @param string $source   The common source (used to map identical resources together)
     * @param string $external If true the TargetMode is "External"
     * @return string Return the new relationship ID (rId)
     */
    private function addRelationship($type, $target, $source = null, $external = false)
    {
        // start a new relationship part for this docFile, if needed
        if (!isset($this->relationships[$this->docFile])) {
            $this->startRelationshipsPart($this->docFile);
        }

        // alter the target to be relative to the current docFile
        if (!$external
            and $this->docFile != ''
            and strpos($target, '/') !== false
            and strpos($target, '://') == false) {
            $root = explode('/', ltrim($this->relationships[$this->docFile]['root'], '/'));
            $ours = array_filter(explode('/', ltrim(dirname($target), '/')), function($d){ return $d != '.'; });
            $min = min(count($root), count($ours));
            $ofs = 0;
            for ($i=0; $i<$min; $i++) {
                if ($root[$i] == $ours[$i]) {
                    $ofs++;
                } else {
                    break;
                }
            }
            $slice = array_slice($ours, $ofs);
            if ($slice) {
                $target = ($ofs ? implode('/', $slice) . '/' : '') . basename($target);
            } else {
                $target = basename($target);
            }
        }

        $rid = $this->getNextRelationshipId();
        $this->relationships[$this->docFile]['rels'][$rid] = array(
            'Id' => $rid,
            'Type' => $type,
            'Target' => $target,
            'TargetMode' => $external ? "External" : null
        );
        if ($source !== null) {
            $this->relationshipsMap[$this->docFile][$type][$source] = $rid;
        }
        return $rid;
    }

    /**
     * Return the next available Relationship ("rId")
     *
     * @param string $prefix String prefix for the returned ID
     */
    protected function getNextRelationshipId($prefix = 'rId')
    {
        // start a new relationship part for this docFile, if needed
        if (!isset($this->relationships[$this->docFile])) {
            $this->startRelationshipsPart($this->docFile);
        }
        $this->relationships[$this->docFile]['rid'] += 1;
        return $prefix . $this->relationships[$this->docFile]['rid'];
    }

    /**
     * Return the current rId for something.
     *
     * @param string $type   The relationship Type
     * @param string $source The relationship common source
     * @return string Return the rId matching the type+source or null if not found.
     */
    private function getRelationshipId($type, $source)
    {
        if (isset($this->relationshipsMap[$this->docFile][$type][$source])) {
            return $this->relationshipsMap[$this->docFile][$type][$source];
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
            ->addContentType('/docProps/core.xml', 'application/vnd.openxmlformats-package.core-properties+xml', true)
            ->addContentType('/docProps/app.xml', 'application/vnd.openxmlformats-officedocument.extended-properties+xml', true)
            ;
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
     * @param \DOMDocument  $dom       Optional new DOMDocument object.
     * @param boolean       $isWordDom If true the new $dom object will be set
     *                                 as the main wordDom in the object.
     * @return \DOMDocument            New DOM document object or the same
     *                                 object passed in.
     */
    public function setDom(\DOMDocument $dom = null, $isWordDom = false)
    {
        if ($dom === null) {
            $dom = new \DOMDocument('1.0', 'utf-8');
            $dom->xmlStandalone = true;
            $dom->formatOutput = true;
        }
        if (!isset($this->wordDom) or $isWordDom) {
            $this->wordDom = $dom;
        }
        return $dom;
    }

    /**
     * Returns the current DOM object.
     */
    public function getDom()
    {
        return $this->wordDom;
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

    /**
     * Perform any cleanup that might be necessary
     */
    protected function cleanup()
    {
        if ($this->unlink) {
            foreach ($this->unlink as $file) {
                @unlink($file);
            }
        }
    }
}
