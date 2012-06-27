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
        $this->data = 'R0lGODlhAQABAPAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';   // dot.gif
        $this->dotgif = 'data:image/gif;base64,' . $this->data;

        if (!@file_exists($this->jpg)) {
            $this->markTestSkipped("Resource image not available: \"$this->jpg\"");
        }
    }

    public function testImage()
    {
        $img = new Image($this->jpg, array('width' => 10, 'height' => 10));

        $this->assertEquals($this->jpg, $img->getSource(), '->getSource() returned source');
        $this->assertEquals(150, $img->getWidth(), '->getWidth() returned width');
        $this->assertEquals(150, $img->getHeight(), '->getHeight() returned height');
        $this->assertEquals(10,  $img->getWidth(true), '->getWidth(true) returned overridden width');
        $this->assertEquals(10,  $img->getHeight(true), '->getHeight(true) returned overridden height');
        $this->assertEquals('image/jpeg', $img->getContentType(), '->getContentType() returned content type');
        $this->assertEquals('jpg', $img->getExtension(), '->getExtension() returned extension');
        $this->assertFalse($img->isRemoteFile(), '->isRemoteFile() returned false for local file');
        $this->assertEquals(base64_encode(file_get_contents($this->jpg)), base64_encode($img->getData()), '->getData() returned valid data for physical image');

        $img = new Image($this->dotgif);
        $this->assertEquals($this->data, base64_encode($img->getData()), '->getData() returned valid data in-memory image');
        $this->assertFalse($img->isRemoteFile(), '->isRemoteFile() returned false for data:url');

        $img = new Image('http://php.net/images/php.gif');
        $this->assertTrue($img->isRemoteFile(), '->isRemoteFile() returned false for remote file');
    }

    public function testSave()
    {
        $img = new Image($this->jpg);
        $tmp = tempnam(sys_get_temp_dir(), 'ImageTest_');
        $img->save($tmp);
        $this->assertEquals($img->getData(), file_get_contents($tmp), '->save() saved image.');
        @unlink($tmp);
    }

    /**
     * @expectedException PHPDOC\Element\ElementException
     */
    public function testSaveException()
    {
        $img = new Image($this->jpg);
        $tmp = '/somewhere/bad/that/does/not/exist/anywhere/ImageTest_blah';
        $img->save($tmp);       // throws ElementException
    }

    /**
     * @expectedException PHPDOC\Element\ElementException
     */
    public function testImageException()
    {
        $img = new Image('bad/file/that/does/not/exist.wtf');
        $img->getWidth();   // throws ElementException
    }

    /**
     * @expectedException PHPDOC\Element\ElementException
     */
    public function testImageEncodingException()
    {
        $img = new Image(str_replace('base64', 'badenc', $this->dotgif));
        $img->getData();    // throws ElementException
    }
}
