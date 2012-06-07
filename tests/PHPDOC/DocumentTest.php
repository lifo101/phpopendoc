<?php

use PHPDOC\Document,
    PHPDOC\Element\Section,
    PHPDOC\Element\SectionInterface
    ;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $doc = new Document();
        $this->assertTrue($doc instanceof Document, 'new Document() returns Document object');
    }

    /**
     * @covers PHPDOC\Document::addSection
     * @covers PHPDOC\Document::getSection
     * @covers PHPDOC\Document::offsetSet
     * @covers PHPDOC\Document::offsetGet
     */
    public function testAddSection()
    {
        $doc = new Document();
        $sec = $doc->addSection();
        $this->assertTrue($sec instanceof SectionInterface, '->addSection() creates a new section');

        $this->assertNotEmpty($sec->getName(), '->addSection() automatically assigns a name');
        
        $sec = new Section();
        $expected = $doc->addSection($sec);
        $this->assertSame($expected, $sec, '->addSection($object) added a pre-existing section');
        
        $expected = new Section();
        $doc['new'] = $expected;
        $this->assertSame($expected, $doc->getSection('new'), '$doc[...] added a pre-existing section');
        $this->assertEquals('new', $expected->getName(), '$doc[...] automatically updated section name');
        
        $doc[] = $expected;
        $this->assertSame($expected, $doc[ $expected->getName() ], '$doc[] added a pre-existing section (no offset)');
    }
    
    /**
     * @covers PHPDOC\Document::offsetSet
     * @expectedException UnexpectedValueException
     */
    public function testAddSectionException()
    {
        $doc = new Document();
        $doc['new'] = new \DateTime();	// throws UnexpectedValueException
    }

    /**
     * @covers PHPDOC\Document::getSection
     * @covers PHPDOC\Document::getSections
     * @covers PHPDOC\Document::offsetGet
     * @covers PHPDOC\Document::offsetExists
     */
    public function testGetSection()
    {
        $doc = new Document();
        $expected = $doc->addSection('test');
    
        $this->assertTrue(isset($doc['test']), 'isset($doc[...]) returned true');
        $this->assertSame($expected, $doc->getSection(), '->getSection() returns current section');
        $this->assertSame($expected, $doc->getSection('test'), '->getSection(\'name\') returns section');
        $this->assertSame($expected, $doc['test'], '$doc[\'name\'] returns section');
        
        $this->assertNotEmpty($doc->getSections(), '->getSections() returns array');
    }

    /**
     * @covers PHPDOC\Document::getSection
     * @expectedException OutOfBoundsException
     */
    public function testGetCurrentSectionException()
    {
        $doc = new Document();
        $doc->getSection();		// throws OutOfBoundsException (since no sections have been added)
    }

    /**
     * @covers PHPDOC\Document::getSection
     * @covers PHPDOC\Document::offsetGet
     * @expectedException OutOfBoundsException
     */
    public function testGetSectionException()
    {
        $doc = new Document();
        $doc->addSection();
        $doc['does_not_exist'];	// throws OutOfBoundsException
    }

    /**
     * @covers PHPDOC\Document::getSection
     * @expectedException OutOfBoundsException
     */
    public function testGetUnknownSectionException()
    {
        $doc = new Document();
        $doc->addSection();
        $doc->getSection('does_not_exist');	// throws OutOfBoundsException
    }

    /**
     * @covers PHPDOC\Document::hasSection
     * @covers PHPDOC\Document::removeSection
     * @covers PHPDOC\Document::offsetUnset
     */
    public function testHasAndRemoveSection()
    {
        $doc = new Document();
        $sec = $doc->addSection('foobar');
        
        $this->assertTrue($doc->hasSection('foobar'), '->hasSection() returns true');
        $doc->removeSection('foobar');
        $this->assertFalse($doc->hasSection('foobar'), '->removeSection() removed a named section');
    
        $sec = $doc->addSection('baz');
        unset($doc['baz']);
        $this->assertFalse($doc->hasSection('baz'), 'unset($doc[...]) removed a named section');
    }
    
    /**
     * @covers PHPDOC\Document::count
     */
    public function testCountable()
    {
        $doc = new Document();
        $doc->addSection();
        $doc->addSection();
        $this->assertEquals(2, count($doc), 'count() returns proper value');
        $this->assertEquals(2, $doc->count(), '->count() returns proper value');
    }
}
