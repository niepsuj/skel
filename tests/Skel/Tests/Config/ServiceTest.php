<?php

namespace Skel\Tests\Config;

use PHPUnit\Framework\TestCase;
use Skel\Config\Service;

class ServiceTest extends TestCase
{
    protected static $path = TEST_ROOT_PATH.'/Fixtures/config';

    protected static function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    public function testConstructorPath()
    {
        $service = new Service(self::$path);
        $this->assertAttributeEquals(self::$path, 'path', $service);
    }

    public function testConstructorDefaultEnv()
    {
        $service = new Service(self::$path);
        $this->assertAttributeEquals('production', 'env', $service);
    }

    public function testConstructorEnv()
    {
        $service = new Service(self::$path, 'stage');
        $this->assertAttributeEquals('stage', 'env', $service);
    }

    public function testResolve()
    {
        $service = new Service(self::$path);
        $file = self::invokeMethod($service, 'resolve', ['test']);

        $this->assertSame(
            self::$path.'/test.json',
            $file
        );
    }

    public function testResolveEnv()
    {
        $service = new Service(self::$path, 'stage');
        $file = self::invokeMethod($service, 'resolve', ['test']);

        $this->assertSame(
            self::$path.'/test.stage.json',
            $file
        );
    }

    public function testLoad()
    {
        $service = new Service(self::$path);
        $config = $service->load('test');

        $this->assertTrue(is_array($config));

        $this->assertArrayHasKey('test', $config);
        $this->assertArrayHasKey('test2', $config);
        $this->assertTrue(is_array($config['test2']));
    }

    public function testLoadFlatten()
    {
        $service = new Service(self::$path);
        $config = $service->load('test',true);

        $this->assertArrayHasKey('test2.test1', $config);
    }

    public function testLoadFlattenNumericKeys()
    {
        $service = new Service(self::$path, 'stage');
        $config = $service->load('test', true);

        $this->assertArrayHasKey('test2.0', $config);
    }

    public function testLoadFlattenDefaultPrefix()
    {
        $service = new Service(self::$path);
        $config = $service->load('test', true, true);

        $this->assertArrayHasKey('test.test2.test1', $config);
    }

    public function testLoadFlattenCustomPrefix()
    {
        $service = new Service(self::$path);
        $config = $service->load('test', true, 'prefix');

        $this->assertArrayHasKey('prefix.test2.test1', $config);
    }

    public function testLoadFlattenDefaultPrefixSubdirectory()
    {
        $service = new Service(realpath(self::$path.'/..'));
        $config = $service->load('config/test', true, true);

        $this->assertArrayHasKey('config.test.test2.test1', $config);
    }
}