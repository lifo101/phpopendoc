<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 *
 */
namespace PHPDOC\Element;

use PHPDOC\Property\Properties;

/**
 * The Bookmark element class represents an internal bookmark
 *
 * @example
 * <code>
    $section[] = new Bookmark('bookmark_name', 'My Text');
    // or
    // bookmarks can start/end in different paragraphs
    $section[] = new Paragraph(array(
        "The bolded word is bookmarked: ",
        Bookmark::start('bookmark_name'),
    ));
    $section[] = new Paragraph(array(
        new Text("bookmarked.", array('b' => true)),
        Bookmark::end('bookmark_name'),
        " But this isn't"
    ))
 * </code>
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class Bookmark extends Element implements BookmarkInterface
{
    /**
     * @var array Internal cache of used names
     */
    private static $cache = array();

    /**
     * @var string Last bookmark that was started
     */
    private static $last = null;

    /**
     * @var integer Internal counter for bookmark IDs
     */
    private static $id = 0;

    public function __construct($name, $elements, $properties = null)
    {
        if (isset(self::$cache[$name])) {
            throw new ElementException("Duplicate bookmark name \"$name\"");
        }
        self::$id += 1;
        self::$last = $name;
        self::$cache[$name] = self::$id;

        parent::__construct($properties);
        if (!is_array($elements)) {
            // $elements shouldn't ever be null, otherwise the bookmark won't
            // have any run content to display the bookmark, so we default to
            // using the bookmark name ...
            $elements = ($elements !== null) ? array( $elements ) : array( $name );
        }

        $last = self::$last;    // save last pointer

        // add bookmark markers to beginning and end of element array
        array_unshift($elements, self::start($name));
        $elements[] = self::end($name);

        self::$last = $last;    // restore last pointer

        foreach ($elements as $e) {
            $this->addElement($e);
        }

    }

    public function addElement($element)
    {
        if ($element instanceof ElementInterface) {
            $this->elements[] = $element;
        } elseif ($element !== null) {
            $this->elements[] = new TextRun($element);
        }
    }

    public static function start($name)
    {
        self::$id += 1;
        self::$last = $name;
        self::$cache[$name] = self::$id;
        $b = new BookmarkMark($name, self::$id, 'start');
        return $b;
    }

    public static function end($name = null)
    {
        if ($name === null) {
            $name = self::$last;
        }
        if (!isset(self::$cache[$name])) {
            $trace = debug_backtrace();
            throw new ElementException("Unknown bookmark name \"$name\" referenced at {$trace[0]['file']}:{$trace[0]['line']}");
        }
        $b = new BookmarkMark($name, self::$cache[$name], 'end');
        return $b;
    }

    public static function clear()
    {
        self::$cache = array();
        self::$last = null;
        self::$id = 0;
    }
}
