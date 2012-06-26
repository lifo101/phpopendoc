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
 * The BookmarkStart element class represents an internal bookmark start
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class BookmarkMark extends Element implements BookmarkMarkInterface
{
    protected $type;
    protected $name;
    protected $id;

    public function __construct($name, $id, $type = null)
    {
        $this->name = $name;
        $this->id = $id;
        if ($type === null) {
            $type = 'start';
        }
        if ($type !== 'start' and $type !== 'end') {
            throw new ElementException("Invalid mark type \"$type\" given. Expected \"start\" or \"end\"");
        }
        $this->type = $type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMark()
    {
        return $this->type;
    }

}
