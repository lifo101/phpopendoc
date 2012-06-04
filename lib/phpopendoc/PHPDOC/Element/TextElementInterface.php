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
 * TextElementInterface defines the interface for inline text ranges.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss <lifo101@gmail.com>
 */
interface TextElementInterface extends InlineElementInterface
{
    public function setContent($content);
    public function getContent();
    public function hasContent();
}