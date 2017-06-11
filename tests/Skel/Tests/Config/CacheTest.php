<?php

namespace Skel\Tests\Config;

use PHPUnit\Framework\TestCase;
use Skel\Config\Cache;

class CacheTest extends TestCase
{
    public function testConstructor()
    {
        $cache = new Cache(
            TEST_ROOT_PATH.'/Fixtures/tmp',
            '0.php'
        );

        $this->assertAttributeEquals('0.php','filename', $cache);
        $this->assertAttributeEquals([], 'storage', $cache);
    }

    public function testSave()
    {
        $cache = new Cache(
            TEST_ROOT_PATH.'/Fixtures/tmp',
            '0.php'
        );

        $this->assertFalse($cache->save(), 'should ignore save - nothing changed');

        $cache['test'] = 1;
        $this->assertAttributeEquals(['test' => 1], 'storage', $cache);

        $this->assertTrue($cache->save(), 'should perform save');
        $this->assertFileExists(
            TEST_ROOT_PATH.'/Fixtures/tmp/0.php',
            'should create cache file'
        );

        $data = include(TEST_ROOT_PATH.'/Fixtures/tmp/0.php');
        $this->assertEquals(
            ['test' => 1], $data,
            'cache file should contain previously saved data'
        );
    }

    public function testLoad()
    {
        $cache = new Cache(
            TEST_ROOT_PATH.'/Fixtures/config/tmp',
            'config.php'
        );

        $this->assertEquals(1, $cache['test']);

        $cache = new Cache(
            TEST_ROOT_PATH.'/Fixtures/config/tmp',
            'config-1.php'
        );

        $this->assertArrayNotHasKey('test', $cache);
    }

    public function testCleanup()
    {
        $cache = new Cache(
            TEST_ROOT_PATH.'/Fixtures/tmp',
            '1.php'
        );

        $cache['test'] = 1;
        $cache->save();

        $this->assertFileExists(TEST_ROOT_PATH.'/Fixtures/tmp/1.php');
        $cache->cleanup();
        $this->assertFileNotExists(TEST_ROOT_PATH.'/Fixtures/tmp/1.php');
    }
}