<?php

use PHPDOC\Document,
    PHPDOC\Element\Image
    ;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    protected $jpg;
    
    public function setUp()
    {
        $mediaPath = __DIR__ . '/../../res/media';
        
        $this->jpg = $mediaPath . '/earth.jpg';
        
        if (!@file_exists($this->jpg)) {
            $this->markTestSkipped("Resource image not available: \"$this->jpg\"");
        }
    }

    public function testImage()
    {
        $img = new Image($this->jpg);
        
        $this->assertEquals($this->jpg, $img->getSource(), '->getSource() returned source');
        $this->assertEquals(150, $img->getWidth(), '->getWidth() returned width');
        $this->assertEquals(150, $img->getHeight(), '->getHeight() returned height');
        $this->assertEquals('image/jpeg', $img->getContentType(), '->getContentType() returned content type');
        $this->assertEquals('jpg', $img->getExtension(), '->getExtension() returned extension');
    }

    /**
     * @expectedException PHPDOC\Element\ElementException
     */
    public function testImageException()
    {
        $img = new Image('bad/file/that/does/not/exist.wtf');
        $img->getWidth();   // throws ElementException
    }
}
