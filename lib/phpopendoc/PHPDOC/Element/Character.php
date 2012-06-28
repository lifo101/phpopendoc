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
abstract class Character extends Element implements CharacterInterface
{
    public function getElements()
    {
        return array();
    }

    public function hasElements()
    {
        return false;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getClear()
    {
        return $this->clear;
    }

    public function getInterface()
    {
        return get_class($this) . 'Interface';
    }
}
