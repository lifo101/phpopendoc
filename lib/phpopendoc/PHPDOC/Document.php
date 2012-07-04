<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 *
 */
namespace PHPDOC;

use PHPDOC\Element\Section,
    PHPDOC\Element\SectionInterface,
    PHPDOC\Property\Properties,
    PHPDOC\Property\PropertiesInterface,
    PHPDOC\Style\StyleInterface,
    PHPDOC\Style\ParagraphStyle,
    PHPDOC\Style\ParagraphStyleInterface,
    PHPDOC\Style\TextStyle,
    PHPDOC\Style\TextStyleInterface
    ;

/**
 * The Document class is the main object interface that allows you to create a
 * document structure that can be saved using one of the Document\Writer
 * classes.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class Document implements \IteratorAggregate, \ArrayAccess, \Countable
{
    protected $sections;
    protected $styles;
    protected $defaultStyles;
    protected $currentSection;
    protected $properties;

    public function __construct($properties = null)
    {
        $this->properties = new Properties($properties);
        $this->sections = array();
        $this->styles = array();
        $this->defaultStyles = array();

        // apply default styles, if set ...
        if ($this->properties->has('defaultStyles')) {
            foreach ($this->properties['defaultStyles'] as $k => $v) {
                $method = 'setDefault' . ucfirst(strtolower($k)) . 'Style';
                if (method_exists($this, $method)) {
                    $this->$method($v);
                }
            }
        }
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function setProperty($key, $value)
    {
        $this->properties[$key] = $value;
        return $this;
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

    public function hasStyles()
    {
        return count($this->styles) > 0;
    }

    public function getStyle($name)
    {
        if (isset($this->styles[$name])) {
            return $this->styles[$name];
        }
        return false;
    }

    public function getStyles()
    {
        return $this->styles;
    }

    public function getDefaultStyles()
    {
        return $this->defaultStyles;
    }

    /**
     * Add a new style to the document
     *
     * @param Style $style The style to add
     */
    public function addStyle(StyleInterface $style)
    {
        $this->styles[ strtolower($style->getId()) ] = $style;
        //$this->styles[ strtolower($style->getName()) ] = $style;
        return $style;
    }

    /**
     * Add a default style to the document
     *
     * Shortcut method that allows the caller to add any default style by
     * passing in the proper StyleInterface object.
     *
     * @param StyleInterface $style A Paragraph or Text style
     */
    public function addDefaultStyle(StyleInterface $style)
    {
        $type = $style->getType();
        $method = 'setDefault' . ucfirst(strtolower($type)) . 'Style';
        if (method_exists($this, $method)) {
            $this->$method($style);
        } else {
            throw new \Exception("Unknown default style type \"$type\" given. Expected \"paragraph\" or \"text\"");
        }
    }

    /**
     * Set the default paragraph style.
     *
     * @param mixed $style An array or PropertiesInterface instance
     */
    public function setDefaultParagraphStyle($style)
    {
        $this->defaultStyles['paragraph'] = new ParagraphStyle('DefaultParagraph',
            $style instanceof StyleInterface ? $style->getProperties() : $style
        );
    }

    /**
     * Set the default text style.
     *
     * @param mixed $style An array or PropertiesInterface instance
     */
    public function setDefaultTextStyle($style)
    {
        $this->defaultStyles['text'] = new TextStyle('DefaultText',
            $style instanceof StyleInterface ? $style->getProperties() : $style
        );
    }

    /**
     * Add a new section to the document
     *
     * All elements in a document are presented within sections (each section
     * is essentially a "Page"; but not strictly).
     *
     * @param mixed $name The section name or an instance of SectionInterface
     */
    public function addSection($name = null, $properties = null)
    {
        if ($name instanceof SectionInterface) {
            $section = $name;
        } else {
            $section = new Section($name, $properties);
        }
        $this->currentSection = $section->getName();
        $this->sections[$this->currentSection] = $section;
        return $section;
    }

    /**
     * Return true if the section exists
     *
     * @param string $name The section name
     */
    public function hasSection($name)
    {
        return array_key_exists($name, $this->sections);
    }

    /**
     * Return all sections.
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Return a section by name or the current section if no name given.
     *
     * @param string $name The name of the section. Null for current.
     */
    public function getSection($name = null)
    {
        if ($name === null) {
            if ($this->currentSection === null) {
                throw new \OutOfBoundsException('No sections defined');
            }
            $name = $this->currentSection;
        }

        if (!array_key_exists($name, $this->sections)) {
            throw new \OutOfBoundsException("Unknown section name \"$name\"");
        }

        return $this->sections[$name];
    }

    /**
     * Remove a section by name.
     *
     * No exception is thrown if the section name does not exist.
     *
     * @param string $name The section name
     */
    public function removeSection($name)
    {
        unset($this->sections[$name]);
    }

    /**
     * Return an iterator for the elements in the section
     *
     * @internal Implements \IteratorAggregate
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->sections);
    }

    /**
     * Returns the total sections currently defined.
     *
     * @internal Implements \Countable
     */
    public function count()
    {
        return count($this->sections);
    }

    /**
     * Set property by key name
     *
     * @internal Implements \ArrayAccess
     */
    public function offsetSet($ofs, $value)
    {
        if ($value instanceof SectionInterface) {
            if (!empty($ofs)) {
                $value->setName($ofs);
            }
            $this->addSection($value);
        } elseif ($value instanceof StyleInterface) {
            if (!empty($ofs)) {
                $value->setId($ofs);
            }
            $this->addStyle($value);
        } else {
            $type = gettype($value);
            if ($type == 'object') {
                $type = get_class($value);
            }
            throw new \UnexpectedValueException("Assignment value not an instance of \"SectionInterface\". Got \"$type\" instead.");
        }
    }

    /**
     * Determine if property exists by key name
     *
     * @internal Implements \ArrayAccess
     */
    public function offsetExists($ofs)
    {
        return $this->hasSection($ofs);
    }

    /**
     * Remove property by key name
     *
     * @internal Implements \ArrayAccess
     */
    public function offsetUnset($ofs)
    {
        unset($this->sections[$ofs]);
    }

    /**
     * Get property by key name
     *
     * @internal Implements \ArrayAccess
     */
    public function offsetGet($ofs)
    {
        return $this->getSection($ofs);
    }
}
