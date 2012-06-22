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
 * HeaderFooter class that holds a special section block that is specifically
 * used for the header or footer of a document.
 *
 * Most content that can go into a normal Section can go into a Header or a
 * Footer. This class works exactly like a normal Section.
 *
 * @example
   <code>
    $sec = $doc->addSection();
    $head = $sec->addHeader();
    $head[] = new Text("This is my centered header", array('align' => 'center'));
   </code>
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class HeaderFooter extends Section implements HeaderFooterInterface
{
    protected $position;
    protected $type;

    // @todo The type can actually only have 'default', 'even' and 'first'
    //       according to the OOXML specs. document setting 'evenAndOddHeaders'
    //       is used to make 'odd' page headers... it's kind of stupid really.
    public function __construct($position = 'header', $type = null, $properties = null)
    {
        if ($position != 'header' and $position != 'footer') {
            throw new \UnexpectedValueException("Invalid \$position \"$position\" specified. Must be \"header\" or \"footer\"");
        }
        $this->position = $position;

        if ($type === null or $type == 'both') {
            $type = 'default';
        }
        $this->type = $type;
        parent::__construct($position . ':' . $type, $properties);
    }

    public function getType()
    {
        return $this->type;
    }

    public function getPosition()
    {
        return $this->position;
    }
}
