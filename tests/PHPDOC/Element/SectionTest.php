<?php

use PHPDOC\Document,
    PHPDOC\Element\Section,
    PHPDOC\Element\Text,
    PHPDOC\Element\TextRun
    ;

class SectionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers PHPDOC\Element\Section::__construct
     * @covers PHPDOC\Element\Section::setProperties
     * @covers PHPDOC\Element\Section::getProperties
     * @covers PHPDOC\Element\Section::hasProperties
     * @covers PHPDOC\Element\Section::getInterface
     */
    public function testConstructor()
    {
        $sec = new Section();
        $this->assertInstanceOf('PHPDOC\\Element\\SectionInterface', $sec, 'Section is an instance of SectionInterface');
        $this->assertNotEmpty($sec->getName(), 'Constructor auto-generated a name');

        $sec = new Section(null, array('type' => 'next'));
        $this->assertTrue($sec->hasProperties(), 'has properties is true');
        $this->assertTrue($sec->getProperties()->has('type'), 'constructor accepted properties paramater');

        $this->assertEquals('PHPDOC\\Element\\SectionInterface', $sec->getInterface(), '->getInterface() returns SectionInterface');
    }

    /**
     * @covers PHPDOC\Element\Section::setProperties
     * @expectedException InvalidArgumentException
     */
    public function testPropertiesException()
    {
        $sec = new Section();
        $sec->setProperties(new \DateTime());   // throws \InvalidArgumentException
    }

    /**
     * @covers PHPDOC\Element\Section::setName
     * @covers PHPDOC\Element\Section::getName
     */
    public function testName()
    {
        $sec = new Section();
        $sec->setName('test');
        $this->assertEquals('test', $sec->getName(), '->setName() properly set new name');
        $this->assertInstanceOf(get_class($sec), $sec->setName('test'), '->setName() returned $this');
    }

    /**
     * @covers PHPDOC\Element\Section::addHeader
     * @covers PHPDOC\Element\Section::addFooter
     * @covers PHPDOC\Element\Section::getHeaders
     * @covers PHPDOC\Element\Section::getFooters
     * @covers PHPDOC\Element\Section::hasHeaders
     * @covers PHPDOC\Element\Section::hasFooters
     */
    public function testHeaderFooter() {
        $sec = new Section();
        $h = array();
        $h['header-default'] = $sec->addHeader();
        $h['header-odd'] = $sec->addHeader('odd');
        $h['header-even'] = $sec->addHeader('even');
        $this->assertTrue($sec->hasHeaders(), 'hasHeaders returns true');
        $this->assertEquals($h, $sec->getHeaders(), 'getHeaders returns proper array');

        $f = array();
        $f['footer-default'] = $sec->addFooter();
        $f['footer-odd'] = $sec->addFooter('odd');
        $f['footer-even'] = $sec->addFooter('even');
        $this->assertTrue($sec->hasFooters(), 'hasFooters returns true');
        $this->assertEquals($f, $sec->getFooters(), 'getFooters returns proper array');
    }

    /**
     * @expectedException PHPDOC\Element\SectionException
     */
    public function testHeaderException()
    {
        $sec = new Section();
        $sec->addHeader('default');
        $sec->addHeader('default'); // throws SectionException
    }

    /**
     * @expectedException PHPDOC\Element\SectionException
     */
    public function testFooterException()
    {
        $sec = new Section();
        $sec->addFooter('default');
        $sec->addFooter('default'); // throws SectionException
    }

    /**
     * @covers PHPDOC\Element\Section::get
     * @covers PHPDOC\Element\Section::set
     * @covers PHPDOC\Element\Section::has
     * @covers PHPDOC\Element\Section::remove
     * @covers PHPDOC\Element\Section::count
     * @covers PHPDOC\Element\Section::hasElements
     * @covers PHPDOC\Element\Section::getElements
     * @covers PHPDOC\Element\Section::addElement
     * @covers PHPDOC\Element\Section::offsetSet
     * @covers PHPDOC\Element\Section::getIterator
     */
    public function testSet()
    {
        $sec = new Section();

        $res = $sec->set("test1"); // should produce a Paragraph (ElementInterface)
        $this->assertInstanceOf('PHPDOC\\Element\\ElementInterface', $res, '->set() returned ElementInterface instance');

        $res = $sec->addElement("test2"); // should produce a Paragraph (ElementInterface)
        $this->assertInstanceOf('PHPDOC\\Element\\ElementInterface', $res, '->addElement() returned ElementInterface instance');

        $sec['test'] = "test3";
        $res = $sec->get('test'); // should produce a Paragraph (ElementInterface)
        $this->assertTrue($sec->has('test'), '->has() returns true');
        $this->assertInstanceOf('PHPDOC\\Element\\ElementInterface', $res, '->get() returned ElementInterface instance');
        $this->assertInstanceOf('PHPDOC\\Element\\ElementInterface', $sec['test'], '$sec[...] returned ElementInterface instance');

        $this->assertTrue($sec->hasElements(), 'hasElements returns true');

        $list = $sec->getElements();
        $this->assertEquals(3, count($sec), 'count($sec) returns proper number');
        $this->assertEquals(3, count($list), '->getElements returns elements');

        $list = array();
        foreach ($sec as $k => $e) {
            $list[$k] = $e;
        }
        $this->assertEquals($sec->getElements(), $list, '->getIterator() works');

        $sec->remove('test');
        $this->assertFalse($sec->has('test'), '->has() returns false');

        // @todo set() actually returns a Paragraph, but has no unique interface
        //       so I can't test for that yet. In the future this should be
        //       refactored to check for ParagraphInterface.
        $res = $sec->set(new Text("test"));
        $this->assertInstanceOf('PHPDOC\\Element\\ElementInterface', $res, '->set(Text(...)) returns ElementInterface');
    }

    /**
     * @covers PHPDOC\Element\Section::set
     * @expectedException UnexpectedValueException
     */
    public function testSetException()
    {
        $sec = new Section();
        $sec->set(new \DateTime()); // throws UnexpectedValueException
    }

}
