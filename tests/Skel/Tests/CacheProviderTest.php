<?php

namespace Skel\Tests;

use Doctrine\ORM\Mapping\Cache;
use PHPUnit\Framework\TestCase;
use Silex\Application;
use Skel\CacheProvider;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class CacheProviderTest extends TestCase
{
    public function testRegisterApcu()
    {
        $app = new Application([
            'name' => 'test',
            'version' => 0
        ]);

        $app->register(new CacheProvider, [
            'cache.adapter' => 'apcu'
        ]);

        $this->assertTrue(
            $app['cache'] instanceof ApcuAdapter,
            'Should be instance of ApcuAdapter'
        );

        $test = $app['cache']->getItem('test');
        $test->set('test value');

        $this->assertTrue(
            $app['cache']->save($test),
            'Should return true while save'
        );
    }

    public function testRegisterArray()
    {
        $app = new Application([
            'name' => 'test',
            'version' => 0
        ]);

        $app->register(new CacheProvider, [
            'cache.adapter' => 'array'
        ]);

        $this->assertTrue(
            $app['cache'] instanceof ArrayAdapter,
            'Should be instance of ArrayAdapter'
        );

        $test = $app['cache']->getItem('test');
        $test->set('test value');

        $this->assertTrue(
            $app['cache']->save($test),
            'Should return true while save'
        );
    }

    public function testRegisterFilesystem()
    {
        $app = new Application([
            'name'      => 'test',
            'version'   => 0,
            'tmp.path'  => TEST_ROOT_PATH.'/Fixtures/tmp'
        ]);

        $app->register( new CacheProvider(), [
            'cache.adapter' => 'filesystem',
        ]);

        $this->assertTrue(
            $app['cache'] instanceof FilesystemAdapter,
            'Should be instance of FilesystemAdapter'
        );

        $test = $app['cache']->getItem('test');
        $test->set('test value');

        $this->assertTrue(
            $app['cache']->save($test),
            'Should return true while save'
        );
    }

    public function testRegisterMemcached()
    {
        $app = new Application([
            'name'      => 'test',
            'version'   => 0
        ]);

        $app->register( new CacheProvider, [
            'cache.adapter' => 'memcached',
        ]);

        $this->assertTrue(
            $app['cache'] instanceof MemcachedAdapter,
            'Should be instance of MemcachedAdapter'
        );

        $test = $app['cache']->getItem('test');
        $test->set('test value');

        $this->assertTrue(
            $app['cache']->save($test),
            'Should return true while save'
        );
    }

    public function testRegisterRedis()
    {
        $app = new Application([
            'name'      => 'test',
            'version'   => 0
        ]);

        $app->register( new CacheProvider, [
            'cache.adapter' => 'redis'
        ]);

        $this->assertTrue(
            $app['cache'] instanceof RedisAdapter,
            'Should be instance of RedisAdapter'
        );

        $test = $app['cache']->getItem('test');
        $test->set('test value');

        $this->assertTrue(
            $app['cache']->save($test),
            'Should return true while save'
        );
    }
}