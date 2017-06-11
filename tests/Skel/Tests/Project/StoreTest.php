<?php

namespace Skel\Tests\Project;

use PHPUnit\Framework\TestCase;
use Skel\Project\Store;

/**
 * Class StoreTest
 * @package Skel\Tests\Project
 */
class StoreTest extends TestCase
{
    /**
     * @covers Store::offsetGet
     * @covers Store::offsetExists
     * @covers Store::offsetSet
     * @covers Store::offsetUnset
     * @covers Store::get
     * @covers Store::exists
     * @covers Store::set
     * @covers Store::clean
     */
    public function testStore()
    {
        $store = new Store();
        $store['test'] = 1;
        $this->assertEquals(1, $store['test']);
        $this->assertTrue(isset($store['test']));
        unset($store['test']);
        $this->assertFalse(isset($store['test']));

        $result = $store->set('test', 1);
        $this->assertEquals($result, $store, 'set method should return store itself');
        $this->assertEquals(1, $store->get('test'));
        $this->assertTrue($store->exists('test'));
        $result = $store->clean('test');
        $this->assertEquals($result, $store, 'clean method should return store itself');
        $this->assertFalse($store->exists('test'));
    }

    /**
     * @covers Store::merge
     * @covers Store::store
     */
    public function testMerge()
    {
        $store = new Store();
        $store['test'] = 1;

        $result = $store->merge([
            'test2' => 2
        ]);

        $this->assertEquals($result, $store, 'merge method should return store itself');
        $this->assertAttributeEquals(
            ['test' => 1, 'test2' => 2],
            'store',
            $store
        );

        $this->assertEquals(
            ['test' => 1, 'test2' => 2],
            $store->store()
        );
    }
}