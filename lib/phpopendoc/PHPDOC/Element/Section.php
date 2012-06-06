<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Element;

/**
 * Section element is a wrapper for a single section within a document.
 *
 * A Aection is usually the same as a document "Page" (but not strictly).
 * A section contains 1 or more elements that make up the content for the
 * document. For example: Paragraphs of texts, images, tables, etc...
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class Section implements SectionInterface
{
    protected $name;
    protected $elements;
    
    public function __construct($name = null)
    {
        $this->name = $name === null ? self::guid() : $name;
        $this->elements = array();
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function guid()
    {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535), mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)
        );
    }
    
    public function getElements()
    {
        return $this->elements;
    }
    
    public function get($ofs)
    {
        return $this->has($ofs) ? $this->elements[$ofs] : null;
    }
    
    public function has($ofs)
    {
        return array_key_exists($ofs, $this->elements);
    }
    
    public function set($value, $ofs = null)
    {
        if (!is_array($value)) {
            $value = array( $value );
        }
        foreach ($value as $element) {
            // @TODO This needs to be smarter to detect other elements, for
            //       example, If I add an Image outside of a paragraph it needs
            //       to be wrapped automatically as well...
            // @TODO Alternate: Instead of performing this logic, I could
            //       blindly add elements and then allow the Document\Writer
            //       to handle it as-needed. That might make more sense.
            if (is_string($element)) {
                $element = new Paragraph($element);
            } elseif (($element instanceof TextInterface) or ($element instanceof TextRunInterface)) {
                $element = new Paragraph($element);
            } elseif (!($element instanceof ElementInterface)) {
                $type = gettype($element);
                if ($type == 'object') {
                    $type = get_class($element);
                }
                throw new \UnexpectedValueException("Assignment value not an instance of \"ElementInterface\". Got \"$type\" instead.");
            }
    
            if ($ofs !== null) {
                $this->elements[$ofs] = $element;
            } else {
                $this->elements[] = $element;
            }
        }

        return $this;
    }
    
    /**
     * Returns the total elements currently defined.
     *
     * @internal Implements \Countable
     */
    public function count()
    {
        return count($this->elements);
    }
    
    /**
     * Add an element.
     *
     * Plain strings are transformed into a TextRun() instance.
     *
     * @internal Implements \ArrayAccess
     */
    public function offsetSet($ofs, $value)
    {
        return $this->set($value, $ofs);
    }
    
    /**
     * Determine if element exists
     *
     * @internal Implements \ArrayAccess
     */
    public function offsetExists($ofs)
    {
        return $this->has($ofs);
    }
    
    /**
     * Remove element
     *
     * @internal Implements \ArrayAccess
     */
    public function offsetUnset($ofs)
    {
        unset($this->elements[$ofs]);
    }
    
    /**
     * Get element
     *
     * @internal Implements \ArrayAccess
     */
    public function offsetGet($ofs)
    {
        return $this->get($ofs);
    }
}
