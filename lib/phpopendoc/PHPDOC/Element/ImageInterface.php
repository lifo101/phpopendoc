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
     * Return the raw data for the image
     *
     * @return Returns the raw data buffer of the image
     */
    public function getData();

    /**
     * Return true if the image is a file.
     *
     * It's possible for images to be a "data:image/XXX;..." string buffers
     *
     * @return boolean Returns true if the image source points to a file.
     */
    public function isFile();

    /**
     * Return the width of the image
     *
     * @param bool $allowOverride If true the element properties 'width' will
     *                            be returned instead of the actual size.
     */
    public function getWidth($allowOverride = false);

    /**
     * Return the height of the image
     *
     * @param bool $allowOverride If true the element properties 'height' will
     *                            be returned instead of the actual size.
     */
    public function getHeight($allowOverride = false);

    /**
     * Return the mimetype of the image
     */
    public function getContentType();

}
