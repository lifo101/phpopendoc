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
    
    /**
     * generate a "random" GUID
     * @codeCoverageIgnore
     */
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
    
    /**
     * Add an element to the section.
     *
     * Elements can be an array. If non-block elements are passed in they will
     * be transformed into a Paragraph or other appropriate block element
     * before it's added to the Section.
     *
     * @param mixed $value Plain string, or other ElementInterface object.
     * @param mixed $ofs   Key name offset to insert element. Optional.
     * @return mixed       Returns the object reference that was added. If an
     *                     array of elements were passed in an array is returned
     *                     instead.
     */
    public function set($value, $ofs = null)
    {
        if (!is_array($value)) {
            $value = array( $value );
        }
        $return = array();
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
    
            $return[] = $element;
            if ($ofs !== null) {
                $this->elements[$ofs] = $element;
                unset($ofs);    // don't use it a second time ... 
            } else {
                $this->elements[] = $element;
            }
        }

        return count($return) == 1 ? $return[0] : $return;
    }

    /**
     *
     */
    public function remove($ofs)
    {
        unset($this->elements[$ofs]);
        return $this;
    }
    
    /**
     * Return an iterator for the elements in the section
     * 
     * @internal Implements \IteratorAggregate
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
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
     * @codeCoverageIgnore
     */
    public function offsetSet($ofs, $value)
    {
        $this->set($value, $ofs);
    }
    
    /**
     * Determine if element exists
     *
     * @internal Implements \ArrayAccess
     * @codeCoverageIgnore
     */
    public function offsetExists($ofs)
    {
        return $this->has($ofs);
    }
    
    /**
     * Remove element
     *
     * @internal Implements \ArrayAccess
     * @codeCoverageIgnore
     */
    public function offsetUnset($ofs)
    {
        $this->remove($ofs);
    }
    
    /**
     * Get element
     *
     * @internal Implements \ArrayAccess
     * @codeCoverageIgnore
     */
    public function offsetGet($ofs)
    {
        return $this->get($ofs);
    }
}
