<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 *
 */
namespace PHPDOC\Element;

// @codeCoverageIgnoreStart

/**
 * ListItemsInterface defines the interface for a list.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss <lifo101@gmail.com>
 */
interface ListItemsInterface extends \Iterator
{

    /**
     * Create a new ListItems instance.
     *
     * This is a shortcut so the caller doesn't have to assign "new" to a
     * variable before they can build out the list in a chainable fashion.
     *
     * @param mixed $properties List level properties
     * @return ListItems Returns a new ListItems instance.
     */
    public static function create($listId = 0, $properties = null);

    /**
     * Return the unique ID of the list
     */
    public function getId();

    /**
     * Return the list type ID of the list
     */
    public function getListId();

    /**
     * Return the unique ID of the parent list. If no parent exists for the
     * list then its own ID is returned instead.
     */
    public function getParentId();

    /**
     * Return a list of all items. Each item is either a Paragraph or ListItems
     * instance. The caller must recursively call getItems() for each ListItems
     * instance in order to traverse all items properly.
     */
    public function getItems();

    /**
     * Return the current list level.
     */
    public function getLevel();

    /**
     * Add an item to the list.
     *
     * Chainable.
     */
    public function item($element = null, $properties = null);

    /**
     * End the current nested list (or all) and return its parent.
     *
     * Chainable.
     */
    public function end($all = false);

    /**
     * Set the level of any future items added to the list.
     *
     * Shortcut for setLevel
     * Chainable.
     *
     */
    public function level($level);

    /**
     * Start a nested list at the current item location
     *
     * Chainable.
     */
    public function listItems($properties = null);
}
