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
 * Base "Character" class
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class Character extends Element implements CharacterInterface
{
    public function hasContent()
    {
         return true;
    }
    
    public function getElements()
    {
        return array();
    }
    
    public function hasElements()
    {
        return false;
    }
    
}