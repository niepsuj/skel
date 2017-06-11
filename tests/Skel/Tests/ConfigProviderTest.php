<?php

namespace Skel\Tests;

use PHPUnit\Framework\TestCase;
use Silex\Application;
use Skel\Config\Cache;
use Skel\Config\Service;
use Skel\ConfigProvider;
use Skel\Event\CleanupEvent;
use Skel\ProjectEvents;

class ConfigProviderTest extends TestCase
{

    public function testRegister()
    {
        $app = new Application([
            'root.path' => TEST_ROOT_PATH.'/Fixtures',
            'tmp.path' => TEST_ROOT_PATH.'/Fixtures/tmp',
            'env' => 'production'
        ]);

        $app->register(new ConfigProvider);
        $this->assertArrayHasKey('config.path', $app);
        $this->assertArrayHasKey('config', $app);
        $this->assertArrayHasKey('config.cache', $app);
        $this->assertArrayHasKey('config.cache.path', $app);
        $this->assertArrayHasKey('config.cache.filename', $app);
        $this->assertTrue($app['config'] instanceof Service);
        $this->assertTrue($app['config.cache'] instanceof Cache);
        $this->assertEquals(TEST_ROOT_PATH.'/Fixtures/config', $app['config.path']);
        $this->assertEquals(TEST_ROOT_PATH.'/Fixtures/tmp/config', $app['config.cache.path']);
        $this->assertEquals('config.php', $app['config.cache.filename']);
    }

    public function testRegisterOverwriteDefaults()
    {
        $app = new Application([
            'env' => 'stage',
            'version' => 0
        ]);

        $app->register(new ConfigProvider, [
            'config.path' => TEST_ROOT_PATH.'/Fixtures/config',
            'tmp.path' => TEST_ROOT_PATH.'/Fixtures/tmp',
            'config.cache' => []
        ]);

        $this->assertAttributeEquals(
            TEST_ROOT_PATH.'/Fixtures/config',
            'path',
            $app['config']
        );

        $this->assertAttributeEquals(
            [],
            'cache',
            $app['config']
        );
    }

    public function testRegisterSaveOnTerminateAndCleanupOnAppEvent()
    {
        $app = new Application([
            'env'           => 'stage',
            'root.path'     => TEST_ROOT_PATH.'/Fixtures',
            'tmp.path'      => TEST_ROOT_PATH.'/Fixtures/tmp',
            'cleanup.scope' => new \ArrayObject()
        ]);

        $app->register(new ConfigProvider);
        $data = $app['config']->load('test');

        ob_start();
        $app->run();
        ob_end_clean();

        $this->assertFileExists(TEST_ROOT_PATH.'/Fixtures/tmp/config/config.php');

        $result = $app['dispatcher']->dispatch(
            ProjectEvents::CLEANUP,
            new CleanupEvent(['config'])
        );
        $this->assertFileNotExists(TEST_ROOT_PATH.'/Fixtures/tmp/config/config.php');
        $this->assertTrue($result->success('config'));
    }
}