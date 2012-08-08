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
 * FieldInterface defines the interface for a dynamic field block.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss <lifo101@gmail.com>
 */
interface FieldInterface
{
    /**
     * Return the type of field. Generally 'simple' or 'complex'.
     */
    public function getType();

    /**
     * Return the instruction function to be rendered
     */
    public function getInstruction();

    /**
     * Return the parameters of the field.
     *
     * @return string String representation of field parameters
     */
    public function getParams();
}
