<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 *
 */
namespace PHPDOC\Document;

// @codeCoverageIgnoreStart

use PHPDOC\Document;

interface WriterInterface
{
    /**
     * Saves the document to the output stream given.
     *
     * @param mixed    $output   Where to save the document. Implementation dependent.
     */
    public function save($output = null);

    /**
     * Saves the document to the output stream given.
     *
     * This does the same as save() but within a static context.
     *
     * @static
     * @param Document $document Document to save.
     * @param mixed    $output   Where to save the document. Implementation dependent.
     */
    public static function saveDocument(Document $doc, $output = null);
}
