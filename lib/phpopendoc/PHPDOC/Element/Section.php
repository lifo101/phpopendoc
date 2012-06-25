<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Element;

use PHPDOC\Property\Properties,
    PHPDOC\Property\PropertiesInterface;

/**
 * Section element is a wrapper for a single section within a document.
 *
 * A Section is usually the same as a document "Page" (but not strictly).
 * A section contains 0 or more elements that make up the content for the
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
    protected $headers;
    protected $footers;
    protected $properties;
    
    public function __construct($name = null, $properties = null)
    {
        $this->name = $name === null ? self::guid() : $name;
        $this->elements = array();
        $this->headers = array();
        $this->footers = array();
        if ($properties) {
            $this->setProperties($properties);
        } else {
            $this->properties = new Properties();
        }
    }
    
    public function getInterface()
    {
        return get_class($this) . 'Interface';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * generate a "random" GUID
     * @codeCoverageIgnore
     */
    public static function guid()
    {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535), mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)
        );
    }

    public function addElement($element)
    {
        return $this->set($element);
    }
    
    public function addHeader($type = null, HeaderFooterInterface $head = null)
    {
        if ($type === null or $type == 'both') {
            $type = 'default';
        }

        // only one of each type is allowed
        if (isset($this->headers['header-' . $type])) {
            throw new SectionException("A \"$type\" header already exists. Only one of each type is allowed.");
        }

        if ($head === null) {
            $head = new HeaderFooter('header', $type);
        }

        $this->headers['header-' . $type] = $head;
        return $head;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function hasHeaders()
    {
        return count($this->headers) > 0;
    }

    public function hasFooters()
    {
        return count($this->footers) > 0;
    }

    public function getFooters()
    {
        return $this->footers;
    }

    public function addFooter($type = null, HeaderFooterInterface $foot = null)
    {
        if ($type === null or $type == 'both') {
            $type = 'default';
        }
        // only one of each type is allowed
        if (isset($this->footers['footer-' . $type])) {
            throw new SectionException("A \"$type\" footer already exists. Only one of each type is allowed.");
        }
        if ($foot === null) {
            $foot = new HeaderFooter('footer', $type);
        }
        $this->footers['footer-' . $type] = $foot;
        return $foot;
    }
    
    /**
     * Get an element by offset.
     *
     * @param mixed $ofs Offset index or string name.
     */
    public function get($ofs)
    {
        return $this->has($ofs) ? $this->elements[$ofs] : null;
    }

    /**
     * Returns true if the element offset exists.
     *
     * @param mixed $ofs Offset index or string name.
     */    
    public function has($ofs)
    {
        return array_key_exists($ofs, $this->elements);
    }

    /**
     * Remove an element by offset index or string name.
     * 
     * @param mixed $ofs Offset index or string name.
     */    
    public function remove($ofs)
    {
        unset($this->elements[$ofs]);
        return $this;
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
            // All elements must be wrapped in a block element
            if (is_string($element)
                or ($element instanceof LinkInterface)  // Link extends Paragraph but is not truly a block
                or !($element instanceof BlockInterface)) {
                $element = new Paragraph($element, $element->getProperties());
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

    public function setProperties($properties)
    {
        if (is_array($properties)) {
            $properties = new Properties($properties);
        }
        if (!($properties instanceof PropertiesInterface)) {
            $type = gettype($properties);
            if ($type == 'object') {
                $type = get_class($properties);
            }
            throw new \InvalidArgumentException("Unexpected properties type of \"$type\" given. Expected PropertiesInterface or array.");
        }
        $this->properties = $properties;
    }

    public function getProperties()
    {
        return $this->properties;
    }
    
    public function hasProperties()
    {
        return count($this->properties) > 0;
    }

    public function getElements()
    {
        return $this->elements;
    }
    
    public function hasElements()
    {
        return $this->elements and count($this->elements) > 0;
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
