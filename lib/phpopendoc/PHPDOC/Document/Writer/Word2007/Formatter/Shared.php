<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 *
 */
namespace PHPDOC\Document\Writer\Word2007\Formatter;

use PHPDOC\Element\ElementInterface,
    PHPDOC\Document\Writer\Exception\SaveException;

/**
 * Shared property formats
 */
class Shared
{
    protected $map;
    private $aliases;

    public function __construct() {
        $this->initMap();
    }

    /**
     * Initialize the property map
     *
     * Subclasses should override this to provide a property map.
     */
    protected function initMap($aliases = null)
    {
        $this->map = array();
        $this->aliases = array();
        if (is_array($aliases) and count($aliases)) {
            $this->aliases = $aliases;
        }
    }

    /**
     * Create a new properties DOM object for the element.
     *
     * @param mixed     $element The element to extract properties from
     * @param \DOMNode  $root    The DOMNode to interface with
     * @return boolean  Return true if any properties were processed.
     */
    public function format(ElementInterface $element, \DOMNode $root)
    {
        $modified = false;
        foreach ($element->getProperties() as $name => $val) {
            $type = $this->lookup($name);
            if ($type) {
                if ($this->process($type, $name, $val, $element, $root)) {
                    $modified = true;
                }
            }
        }
        return $modified;
    }

    /**
     * Process a property
     *
     * @param string           $type    The translation type.
     * @param string           $name    The original property name.
     * @param mixed            $val     The property value or array.
     * @param ElementInterface $element The element being processed.
     * @param \DOMNode         $root    The DOM node to update.
     * @return boolean Return true if the property was processed
     */
    public function process($type, $name, $val, $element, $root)
    {
        $ret = false;
        $method = 'process_' . $type;
        if (method_exists($this, $method)) {
            $ret = $this->$method($this->lookupAlias($name), $val, $element, $root);
            if (!is_bool($ret)) {
                // failsafe for when I forget to return true from one of my
                // process methods and i spend several minutes wondering why
                // the property is not being saved to the document...
                throw new \UnexpectedValueException(get_class($this) . "::$method did not return a boolean. Got " . gettype($ret) . " instead");
            }
        } else {
            // @todo Should an exception be thrown for unknown properties?
        }
        return $ret;
    }

    /**
     * Process simple boolean property
     *
     * @param string           $name    The original property name.
     * @param mixed            $val     The property value or array.
     * @param ElementInterface $element The element being processed.
     * @param \DOMNode         $root    The DOM node to update.
     * @return boolean Return true if the property was processed
     */
    protected function process_bool($name, $val, ElementInterface $element, \DOMNode $root)
    {
        return $this->appendSimpleValue($root, $name, $this->getOnOff($val));
    }

    protected function process_decimal($name, $val, ElementInterface $element, \DOMNode $root)
    {
        return $this->appendSimpleValue($root, $name, intval($val));
    }

    protected function process_text($name, $val, ElementInterface $element, \DOMNode $root)
    {
        return $this->appendSimpleValue($root, $name, $val);
    }

    protected function process_align($name, $val, ElementInterface $element, \DOMNode $root)
    {
        static $valid = array(
            'both', 'justify', 'left', 'right', 'center', 'distribute',
            'highKashida', 'lowKashida', 'mediumKashida', 'thaiDistribute'
        );

        if ($val == 'justify') {
            $val = 'both';
        }
        if (!in_array($val, $valid)) {
            throw new SaveException("Invalid justify value \"$val\". Must be one of: " . implode(',',$valid));
        }

        return $this->appendSimpleValue($root, $name, $val);
    }

    protected function process_font($name, $val, ElementInterface $element, \DOMNode $root)
    {
        static $attrs = array('ascii', 'cs', 'eastAsia', 'hAnsi');

        if (!is_array($val)) {
            $val = array(
                'ascii' => $val,
                'cs' => $val,
                'hAnsi' => $val,
                //'eastAsia' => $val,
            );
        }

        $prop = $root->ownerDocument->createElement('w:' . $name);
        foreach ($val as $k => $v) {
            $prop->appendChild(new \DOMAttr('w:' . $k, $v));
        }
        $root->appendChild($prop);
        return true;
    }

    protected function process_tblWidth($name, $val, ElementInterface $element, \DOMNode $root)
    {
        $type = 'dxa';                          // default to twips
        $w = floatval($val);
        if ($val === null or $val == 'null' or $val == 'nil') {
            $type = 'nil';
            $w = 0;
        } elseif (substr($val, -1) == '%') {
            $type = 'pct';
            $w = $w * 50;                       // Fiftieth of a percent
        } elseif (substr($val, -2) == 'pt') {
            $w = Translator::pointToTwip($w);
        } elseif (substr($val, -2) == 'px') {
            $w = Translator::pixelToTwip($w);
        } elseif (substr($val, -2) == 'in') {
            $w = Translator::inchToTwip($w);
        }

        $dom = $root->ownerDocument;

        $prop = $dom->createElement('w:' . $name);
        $prop->appendChild(new \DOMAttr('w:type', $type));
        $prop->appendChild(new \DOMAttr('w:w', $w));

        $root->appendChild($prop);
        return true;
    }

    protected function process_shading($name, $val, ElementInterface $element, \DOMNode $root)
    {
        static $attrs = array('val','color','fill');
        static $valid = array(
            null, 'nil', 'clear', 'solid', 'horzStripe', 'vertStripe',
            'reverseDiagStripe', 'diagStripe', 'horzCross', 'diagCross',
            'thinHorzStripe', 'thinVertStripe', 'thinReverseDiagStripe',
            'thinDiagStripe', 'thinHorzCross', 'thinDiagCross', 'pct5',
            'pct10', 'pct12', 'pct15', 'pct20', 'pct25', 'pct30', 'pct35',
            'pct37', 'pct40', 'pct45', 'pct50', 'pct55', 'pct60', 'pct62',
            'pct65', 'pct70', 'pct75', 'pct80', 'pct85', 'pct87', 'pct90',
            'pct95',
        );

        //if (!in_array($val, $valid)) {
        //    throw new SaveException("Invalid shading value \"$val\". Must be one of: " . implode(',',$valid));
        //}

        // if $val is a string then assume its a simple color value
        if (!is_array($val)) {
            $val = array(
                'val' => 'clear',
                'color' => 'auto',
                'fill' => $val,
            );
        }

        $dom = $root->ownerDocument;
        $prop = $dom->createElement('w:' . $name);

        foreach ($val as $k => $v) {
            if (!in_array($k, $attrs)) {
                continue;
            }

            $prop->appendChild(new \DOMAttr('w:' . $k, $v));
        }

        $root->appendChild($prop);
        return true;
    }

    /**
     * Lookup a property name and return the translation type for it
     *
     * @return string The translation type or null if not defined in map
     * @param string $name The property name/alias to lookup
     */
    public function lookup($name)
    {
        $name = $this->lookupAlias($name);
        if (isset($this->map[$name])) {
            return $this->map[$name];
        }
        return null;
    }

    /**
     * Lookup a property alias
     *
     * @return Returns the original name if no alias is defined.
     * @param string $name The original property name.
     */
    protected function lookupAlias($name, $aliases = null)
    {
        if (!$aliases) {
            $aliases =& $this->aliases;
        }
        if (isset($aliases[$name])) {
            return $aliases[$name];
        }
        return $name;
    }

    /**
     * Assign a simple value to the root, <w:$name $key="$val"/>
     */
    public function appendSimpleValue(\DOMNode $root, $name, $val, $key='w:val')
    {
        if (is_bool($val)) {
            $val = self::getOnOff($val);
        }
        if ($val !== null and $val !== '') {
            $node = $root->ownerDocument->createElement('w:' . $name);
            $node->appendChild(new \DOMAttr($key, $val));
            $root->appendChild($node);
        }
        return true;
    }

    /**
     * Return 'on', 'off' or null based on the value given.
     */
    public function getOnOff($value)
    {
        if ($value === null) {
            return null;
        }
        if (is_bool($value)) {
            return $value ? 'on' : 'off';
        }
        $value = strtolower($value);
        if (in_array($value, array('on', 'true', 'yes', '1', 1))) {
            return 'on';
        }
        if (in_array($value, array('off', 'false', 'no', '0', 0))) {
            return 'off';
        }
        return null;
    }
}
