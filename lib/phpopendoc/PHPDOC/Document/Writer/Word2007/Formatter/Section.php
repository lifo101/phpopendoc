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
    PHPDOC\Element\SectionInterface,
    PHPDOC\Document\Writer\Exception\SaveException,
    PHPDOC\Document\Writer\Word2007\Translator
    ;

/**
 * Creates properties for sections <w:sectPr>
 */
class Section extends Shared
{
    
    /**
     * Property map (used for simple properties)
     */
    private static $propertyMap = array(
        'type'          => 'type',
        
        'page'          => 'pgSz',
        'pageSize'      => 'pgSz',
        'pgsz'          => 'pgSz',
        'pgSz'          => 'pgSz',

        'orient'        => 'orient',
        'orientation'   => 'orient',
        
        'height'        => 'h',
        'h'             => 'h',
        
        'width'         => 'w',
        'w'             => 'w',
        
        'code'          => 'code',
        
        'top'           => 'top',
        'bottom'        => 'bottom',
        'left'          => 'left',
        'right'         => 'right',
    );

    private static $localMap = array();
    
    private static $translateMap = array(
        'pgSz' => array(
            'w'         => 'translate_dimension',
            'h'         => 'translate_dimension',
            'orient'    => null,
            'code'      => null
        ),
        'pgMar' => array(
            
        ),
        'pgBorders' => array(
            
        )
    );
    
    private $pageSize;
    private $pageSizeProp;
    private $borders;
    private $margins;

    public function __construct()
    {
        parent::__construct(self::$propertyMap);
    }

    /**
     *
     */
    //public function format($element, \DOMNode $node)
    //{
    //    if (!($element instanceof ElementInterface) and
    //        !($element instanceof SectionInterface)) {
    //        throw new \InvalidArgumentException("Argument 1 passed to " . __METHOD__
    //                                            . '() must implement interface ElementInterface or SectionInterface. '
    //                                            . 'Class ' . get_class($element) . ' given instead.');
    //    }
    //
    //    if (!$element->hasProperties()) {
    //        return false;
    //    }
    //
    //    $props = $element->getProperties(); //$this->normalizeProperties($element->getProperties());
    //    
    //    $modified = false;
    //    foreach ($props as $key => $val) {
    //        $name = $this->localLookup($key);
    //        if ($name !== null) {
    //            $method = 'process_' . $name;
    //            if (method_exists($this, $method)) {
    //                if ($this->$method($val, $node)) {
    //                    $modified = true;
    //                }
    //            }
    //            continue;
    //        }
    //        
    //        //$name = $this->lookup($key);
    //        //if ($name === null) {   // invalid property
    //        //    continue;
    //        //}
    //        //
    //        //// do not add property if the value is null
    //        //if ($val !== null) {
    //        //    $prop = $node->ownerDocument->createElement('w:' . $name);
    //        //    if ($this->translate($name, $val, $prop)) {
    //        //        $node->appendChild($prop);
    //        //        $modified = true;
    //        //    }
    //        //}
    //    }
    //
    //    if ($this->pageSize->hasAttributes()) {
    //        $node->appendChild($this->pageSize);
    //    }
    //    
    //    return $modified;
    //}

    /**
     * Process <w:pgSz/> properties
     */
    protected function process_pgSz($name, $prop, $element, $node)
    {
        if (!is_array($prop)) {
            return false;
        }
        
        if (!$this->pageSize) {
            $this->pageSize = $node->ownerDocument->createElement('w:pgSz');
            $node->appendChild($this->pageSize);
            $this->pageSizeProp = $prop;
        }

        foreach ($prop as $key => $val) {
            $name = $this->lookup($key);
            if ($name !== null) {
                $this->translate($name, $val, $this->pageSize, self::$translateMap['pgSz']);
            }
        }

        return true;
    }

    //protected function translate_null($name, $val, $node)
    //{
    //    $node->appendChild(new \DOMAttr('w:'.$name, $val));
    //}

    protected function translate_dimension($name, $val, $node)
    {
        $node->appendChild(new \DOMAttr('w:'.$name, Translator::inchToTwip($val)));
    }

    protected function translate_orient($name, $val, $node)
    {
        static $valid = array('landscape', 'portrait');
        
        if ($val !== null and !in_array($val, $valid)) {
            throw new SaveException("Invalid type value \"$val\". Must be one of: " . implode(',',$valid));
        }

        if ($val !== null) {
            
            $node->appendChild(new \DOMAttr('w:'.$name, $val));
        }
    }

    protected function translate_type($name, $val, $node)
    {
        static $valid = array(
            'continuous', 'evenPage', 'oddPage', 'nextPage', 'nextColumn',
        );

        // allow shortcuts for pages, "next" => "nextPage", etc...
        if (in_array($val, array('even','odd','next'))) {
            $val .= 'Page';
        }

        if (!in_array($val, $valid)) {
            throw new SaveException("Invalid type value \"$val\". Must be one of: " . implode(',',$valid));
        }

        return $this->appendSimpleValue($node, $val);
    }

    public function localLookup($name)
    {
        if (isset(self::$localMap[$name])) {
            return self::$localMap[$name];
        }
        return null;
    }

}
