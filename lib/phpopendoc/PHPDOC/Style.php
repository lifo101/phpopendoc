<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 *
 */
namespace PHPDOC;

use PHPDOC\Property\Properties,
    PHPDOC\Property\PropertiesInterface,
    PHPDOC\Style\StyleInterface
    ;

/**
 * The base Style class
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
abstract class Style implements StyleInterface
{
    static $defaults = array(
        'paragraph' => array(
            'basedOn'   => 'Normal',
            'next'      => 'Normal',
            'qFormat'   => true,
        ),
        'text' => array(
            'basedOn'   => 'Normal',
            'next'      => 'Normal',
            'qFormat'   => true,
        ),
    );

    protected $id;
    protected $name;
    protected $properties;

    public function __construct($name, $id = null, $properties = null)
    {
        // if $name is an array then its a properties array. Auto-assign a
        // default name to this object.
        if (is_array($name)) {
            $properties = $name;
            $name = '__' . ucfirst($this->getType());
        }
        $this->name = $name;

        // $id is optional
        if (is_array($id) or ($id instanceof PropertiesInterface)) {
            $properties = $id;
            $id = null;
        }

        // if $id is null then set it to the name w/o spaces
        if ($id === null) {
            $id = self::nameToId($name);
        }
        $this->setId($id);

        $this->properties = new Properties($properties);
        $defaults = isset(self::$defaults[$this->getType()]) ? self::$defaults[$this->getType()] : null;
        if ($defaults) {
            foreach ($defaults as $k => $v) {
                if (!$this->properties->has($k)) {
                    if ($k == 'basedOn' and $name == 'Normal') {
                        // don't allow "Normal" style to be based on itself
                        continue;
                    }
                    $this->properties->set($k, $v);
                }
            }
        }
    }

    public static function create($type, $name, $id = null, $properties = null)
    {
        $class = ucfirst(strtolower($type)) . 'Style';
        if (strpos($class, '\\') === false) {
            $class = __NAMESPACE__ . '\\Style\\' . $class;
        }
        if (!class_exists($class)) {
            $trace = debug_backtrace();
            throw new \Exception("Unknown style type \"$class\" specified at {$trace[0]['file']}:{$trace[0]['line']}");
        }
        $s = new $class($name, $id, $properties);
        return $s;
    }

    public static function nameToId($name)
    {
        return str_replace(' ', '', $name);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        // ignore namespace and "Style" in PHPDOC\Style\ClassnameStyle
        return strtolower(substr(get_class($this), strrpos(get_class($this), '\\')+1, -5));
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getProperties()
    {
        return $this->properties;
    }
}
