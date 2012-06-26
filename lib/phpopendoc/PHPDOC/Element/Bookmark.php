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
    $section[] = Bookmark::create('bookmark_name', 'My Text');
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

    public static function create($name, $elements)
    {

    }

    public static function start($name)
    {

    }

    public static function end($name = null)
    {

    }

}
