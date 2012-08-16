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
    PHPDOC\Property\PropertiesInterface
    ;

/**
 * The ListItems element class represents a list of paragraphs and implements a
 * "Chainable" coding interface to make it easy to build complex lists without
 * breaking up your code.
 *
 * @example
 * <code>
    $list = ListItems::create()
        ->item('Item 1')
        ->item('Item 2')
        ->item('Item 3')
        ->listItems()
            ->item('Sub Item 1')
            ->item('Sub Item 2')
            ->item('Sub Item 3')
        ->end()
        ->item('Item 4')
        ->item('Item 5')
    ->end();
 * </code>
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class ListItems extends Element implements ListItemsInterface, BlockInterface
{
    /**
     * Global next ID auto-increment value
     */
    static public $nextId = 1;

    /**
     * @var array The list of items
     */
    protected $items;

    /**
     * @var integer Current level context of items
     */
    protected $level;

    /**
     * @var integer List type ID. This maps to the abstractNumId in the WordML
     */
    protected $listId;

    /**
     * @var integer Unique ID of the list
     */
    protected $id;

    /**
     * @var ListItems Parent list for nested lists.
     */
    private $parentList;

    /**
     * @var integer Internal position index for \Iterator implementation
     * @internal
     */
    private $_pos;

    public function __construct($listId = 0, $properties = null, $level = null)
    {
        $this->id = self::$nextId++;
        $this->items = array();

        $this->listId = $listId;
        $this->level = $level !== null ? $level : 0;

        // assume a style ID is being passed in if $properties is a string
        if (is_string($properties)) {
            $properties = array( 'style' => $properties );
        }
        parent::__construct($properties);
    }

    public static function create($listId = 0, $properties = null)
    {
        $list = new ListItems($listId, $properties);
        return $list;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getListId()
    {
        return $this->listId;
    }

    public function getParent($deep = false)
    {
        if (!$this->parentList) {
            return null;
        }

        $p = $this->parentList;
        if (!$deep) return $p;

        while ($p) {
            if (!$p->parentList) {
                break;
            }
            $p = $p->parentList;
        }
        return $p;
    }

    public function getParentId()
    {
        $p = $this->getParent(true);
        return $p ? $p->getId() : $this->getId();
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setParent($parent = null)
    {
        $this->parentList = $parent;
        return $this;
    }

    public function setLevel($level)
    {
        $this->level = $level;
        return $this;
    }

    /**
     * Shortcut when used in a chain
     */
    public function level($level)
    {
        return $this->setLevel($level);
    }

    /**
     * Created a new list at the current item context.
     *
     * This is a shortcut that makes it easier to nest lists. In order to jump
     * back to the previous list in the current chain you must call ->end() to
     * end the nested list. When using ListItems::create() and you have nested
     * lists always be sure to call end() otherwise the last nested list
     * will be the one assigned to your variable and not the root list.
     *
     * @param mixed $properties ListItems level properties
     * @return ListItems Returns a new ListItems instance.
     */
    public function listItems($listId = null, $properties = null)
    {
        $list = new ListItems($listId ?: $this->listId, $properties);
        $list->setParent($this);
        $list->setLevel($this->level + 1);

        $this->item($list);
        return $list;
    }

    /**
     * End the current nested list level.
     *
     * This allows the caller to build a nested list structure using one long
     * chain w/o having to break up their code. If no nested list exists then
     * this returns the current list instance.
     *
     * @param boolean $all If true all nested list are ended immediately.
     */
    public function end($all = false)
    {
        if (!$all) {
            return $this->parentList ? $this->parentList : $this;
        }

        $p = $this->parentList;
        while ($p) {
            if ($p->parentList) {
                $p = $p->parentList;
            } else {
                return $p;
            }
        }

        return $this;
    }

    public function item($element = null, $properties = null)
    {
        if (is_array($element)) {
            throw new ElementException("List items may only be a single Paragraph and not an array.");
        }
        if ($element instanceof LinkInterface or
            (
            !($element instanceof BlockInterface) and
            !($element instanceof ListItemsInterface))
            ) {
            $element = new Paragraph($element, $properties);
        }
        $props = $this->properties->all();
        unset($props['id']);
        $element->getProperties()->merge($props);
        $this->items[] = $element;

        return $this;
    }

    /**
     * Implements \Iterator
     */
    public function valid() {
        return isset($this->items[$this->_pos]);
    }

    /**
     * Implements \Iterator
     */
    public function next() {
        $this->_pos++;
    }

    /**
     * Implements \Iterator
     */
    public function current() {
        return $this->items[$this->_pos];
    }

    /**
     * Implements \Iterator
     */
    public function rewind() {
        $this->_pos = 0;
    }

    /**
     * Implements \Iterator
     */
    public function key() {
        return $this->_pos;
    }
}
