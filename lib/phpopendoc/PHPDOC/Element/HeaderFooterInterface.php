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
 * HeaderFooterInterface
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss <lifo101@gmail.com>
 */
interface HeaderFooterInterface
{
    /**
     * Return the type
     *
     * The page type specifies which pages the hdr/ftr will appear. Odd, even
     * or all pages.
     *
     */
    public function getType();

    /**
     * Return the position
     *
     * The position will be "header" or "footer"
     *
     */
    public function getPosition();
}