<?php

namespace Skel\Tests\Project;

use Skel\Project\Registry;
use PHPUnit\Framework\TestCase;

class RegistryTest extends TestCase
{
    /**
     * @covers Registry::push
     * @covers Registry::pop
     * @covers Registry::shift
     * @covers Registry::unshift
     */
    public function testStack()
    {
        $registry = new Registry();
        $registry->push(1);
        $this->assertAttributeEquals(
            [1],
            'registry',
            $registry,
            'After push(1)');
        $registry->unshift(2);
        $this->assertAttributeEquals(
            [2,1],
            'registry',
            $registry,
            'after unshift(2)'
        );

        $registry2 = clone $registry;
        $this->assertEquals(1, $registry->pop());
        $this->assertAttributeEquals(
            [2],
            'registry',
            $registry,
            'after pop'
        );

        $this->assertEquals(2, $registry2->shift());
        $this->assertAttributeEquals(
            [1],
            'registry',
            $registry2,
            'after shift'
        );
    }

    public function testCount()
    {
        $registry = new Registry();
        $registry->set([1, 2]);
        $this->assertEquals(2, count($registry));
    }

    /**
     * @covers Registry::offsetGet
     * @covers Registry::offsetSet
     * @covers Registry::offsetExists
     * @covers Registry::offsetUnset
     * @covers Registry::set
     */
    public function testArrayAccess()
    {
        $registry = new Registry();
        $registry->set([1, 2, 3]);

        $this->assertEquals(1, $registry[0], 'OffsetGet test');
        unset($registry[0]);
        $this->assertEquals(2, $registry[0], 'OffsetUnset test');
        $registry[0] = 1;
        $this->assertEquals(1, $registry[0], 'OffsetSet test');
        $this->assertFalse(isset($registry[2]), 'OffsetExists test');

        $registry[] = 4;
        $this->assertFalse(isset($registry[3]), 'OffsetSet with null offset test');
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testArrayAccessGetException()
    {
        $registry = new Registry();
        $registry->set([1, 2, 3]);
        $registry[3];
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testArrayAccessSetException()
    {
        $registry = new Registry();
        $registry->set([1, 2, 3]);
        $registry[3] = 4;
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testArrayAccessUnsetException()
    {
        $registry = new Registry();
        $registry->set([1, 2, 3]);
        unset($registry[3]);
    }

    public function testMerge()
    {
        $registry = new Registry();
        $registry->set([1,2,3]);
        $registry->merge([4,5,6]);

        $this->assertAttributeEquals(
            [1,2,3,4,5,6],
            'registry',
            $registry
        );
    }

    /**
     * @covers Registry::registry
     * @covers Registry::getIterator
     */
    public function testRegistry()
    {
        $registry = new Registry();
        $registry->set([1,2,3]);
        $this->assertEquals([1,2,3], $registry->registry());
        $this->assertEquals([1,2,3], $registry->getIterator()->getArrayCopy());

    }
}
