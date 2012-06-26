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
 * BookmarkInterface
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss <lifo101@gmail.com>
 */
interface BookmarkInterface
{
    /**
     * Start a new bookmark.
     *
     * @param string $name     Unique bookmark name
     */
    public static function start($name);

    /**
     * End a bookmark.
     *
     * @param string $name  Unique bookmark name. Optional; if not given the
     *                      last bookmark created will be ended.
     */
    public static function end($name = null);

    /**
     * Clear bookmark cache.
     *
     * This is needed if you need to create bookmarks in different documents
     * within the same run of the script.
     */
    public static function clear();
}
