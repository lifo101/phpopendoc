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
 * ImageInterface defines the interface for inline images.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss <lifo101@gmail.com>
 */
interface ImageInterface
{
    /**
     * Save the image source to the specified destination filename.
     *
     * @param string $dest Destination filename.
     */
    public function save($dest);
    
    /**
     * Set the source file/stream for the image
     *
     * @param string $source Rilename or stream resource
     */
    public function setSource($source);

    /**
     * Return the current source file/stream
     */
    public function getSource();

    /**
     * Return the width of the image
     */
    public function getWidth();
    
    /**
     * Return the height of the image
     */
    public function getHeight();
    
    /**
     * Return the mimetype of the image
     */
    public function getMimeType();
    
}