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
    PHPDOC\Document\WriterInterface,
    PHPDOC\Document\Writer\Exception\SaveException,
    PHPDOC\Element\SectionInterface
    ;

class XML implements WriterInterface 
{
    protected $document;
    protected $dom;
    protected $throwSaveException;
    
    public function __construct(Document $document = null)
    {
        $this->document = $document;
        $this->throwSaveException = true;
    }

    /**
     * Saves the document as an XML string.
     */
    public function save($output = null, Document $document = null)
    {
        if ($document == null) {
            if ($this->document === null) {
                if ($this->throwSaveException) {
                    throw new SaveException("No Document defined");
                } else {
                    trigger_error("No Document defined", E_USER_ERROR);
                }
            }
            $document = $this->document;
        }
        
        if (!$this->dom) {
            $this->setDom();
        }
        $this->processDocument($document);
        $this->saveDom($output);
        
        return $this;
    }

    /**
     * Saves the DOM as XML
     */
    protected function saveDom($output = null)
    {
        if ($output === null) {
            $output = 'php://output';
        }

        $fp = @fopen($output, 'wb');
        if (!$fp) {
            if ($this->throwSaveException) {
                throw new SaveException("Error opening output \"$output\" for writing");
            } else {
                trigger_error("Error opening output \"$output\" for writing", E_USER_ERROR);
            }
        }
        fwrite($fp, $this->dom->saveXML());
        fclose($fp);
        
        return $this;
    }
    
    /**
     * Set a new DOMDocument object.
     *
     * Creates a new DOMDocument object. The caller can pass in a new object
     * of their own creation, or pass nothing and a default object will be
     * created instead.
     *
     * @param \DOMDocument $dom Optional; A new DOMDocument object
     */
    public function setDom(\DOMDocument $dom = null)
    {
        if ($dom === null) {
            $dom = new \DOMDocument('1.0', 'utf-8');
            $dom->formatOutput = true;
        }
        $this->dom = $dom;
    }
    
    /**
     * Returns the current DOM object.
     */
    public function getDom()
    {
        return $this->dom;
    }

    /**
     * Shortcut so caller doesn't have to instantiate a new XML() object to
     * save the document.
     *
     * @param Document $document The document to save
     * @param string $output Filename to output XML to
     */
    public static function saveDocument(Document $document, $output = null)
    {
        $writer = new XML();
        return $writer->save($output, $document);
    }

    public function processDocument($document)
    {
        // process main Document
        if ($document instanceof Document) {
            $root = $this->dom->createElement('document');
            $this->dom->appendChild($root);

            $sections = $this->dom->createElement('sections');
            $root->appendChild($sections);
            
            foreach ($document->getSections() as $section) {
                $this->processSection($sections, $section);
            }
            
        } else {
            // process sub node within the document
            $root = $document;
        }
    }
    
    public function processSection(\DOMNode $root, SectionInterface $section)
    {
        $node = $this->dom->createElement('section');
        $node->appendChild(new \DOMAttr('name', $section->getName()));
        $root->appendChild($node);

        if ($section->hasProperties()) {
            foreach ($section->getProperties() as $key => $val) {
                $node->appendChild(new \DOMAttr($key, $val));
            }
        }

        $this->processNode($node, $section);
     
    }

    public function processNode(\DOMNode $root, $element)
    {
        // process all children within the element ...
        foreach ($element->getElements() as $child) {
            // determine the base name of the element (without namespace)
            $name = get_class($child);
            $name = substr($name, strrpos($name, '\\')+1);

            // have processor class process the element
            $className = __CLASS__ . '\\' . $name;
            if (class_exists($className)) {
                $className::process($this, $root, $child);
            } else {
                if ($this->throwSaveException) {
                    throw new SaveException("Element processor for \"$name\" not found in \"$className\"");
                } else {
                    trigger_error("Element processor for \"$name\" not found in \"$className\"", E_USER_WARNING);
                }
            }
        }
    }
    
    /**
     * Configures the writer to throw SaveException on errors.
     *
     * If false trigger_error() is called instead.
     */
    public function setSaveException($value)
    {
        $this->throwSaveException = (bool)$value;
        return $this;
    }
}