<?php

use PHPDOC\Document,
    PHPDOC\Element\Section,
    PHPDOC\Element\Text,
    PHPDOC\Element\TextRun
    ;

class SectionTest extends \PHPUnit_Framework_TestCase
{

    //protected $doc;
    //
    //public function setUp()
    //{
    //    $this->doc = new Document();
    //}
    //
    //public function tearDown()
    //{
    //    unset($this->doc);
    //}

    /**
     * @covers PHPDOC\Element\Section::__construct
     */
    public function testConstructor()
    {
        $sec = new Section();
        $this->assertInstanceOf('PHPDOC\Element\SectionInterface', $sec, 'Section is an instance of SectionInterface');
        $this->assertNotEmpty($sec->getName(), 'Constructor auto-generated a name');
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
     * @covers PHPDOC\Element\Section::get
     * @covers PHPDOC\Element\Section::set
     * @covers PHPDOC\Element\Section::has
     * @covers PHPDOC\Element\Section::remove
     * @covers PHPDOC\Element\Section::count
     * @covers PHPDOC\Element\Section::getElements
     * @covers PHPDOC\Element\Section::offsetSet
     * @covers PHPDOC\Element\Section::getIterator
     */
    public function testSet()
    {
        $sec = new Section();
        
        $res = $sec->set("test1"); // should produce a Paragraph (ElementInterface)
        $this->assertInstanceOf('PHPDOC\\Element\\ElementInterface', $res, '->set() returned ElementInterface instance');

        $sec['test'] = "test2";
        $res = $sec->get('test'); // should produce a Paragraph (ElementInterface)
        $this->assertTrue($sec->has('test'), '->has() returns true');
        $this->assertInstanceOf('PHPDOC\\Element\\ElementInterface', $res, '->get() returned ElementInterface instance');
        $this->assertInstanceOf('PHPDOC\\Element\\ElementInterface', $sec['test'], '$sec[...] returned ElementInterface instance');
        
        $list = $sec->getElements();
        $this->assertEquals(2, count($sec), 'count($sec) returns proper number');
        $this->assertEquals(2, count($list), '->getElements returns elements');

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
