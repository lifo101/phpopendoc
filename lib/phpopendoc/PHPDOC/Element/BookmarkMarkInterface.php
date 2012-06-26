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
 * BookmarkMarkInterface
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss <lifo101@gmail.com>
 */
interface BookmarkMarkInterface
{

    //public function __construct($name, $id, $type = null);

    /**
     * Get the mark type of the bookmark.
     *
     * The mark type is either 'start' or 'end'.
     */
    public function getMark();

    /**
     * Return the bookmark name
     */
    public function getName();

    /**
     * Return the bookmark ID
     */
    public function getId();
}
