<?php

namespace Skel\Tests;

use PHPUnit\Framework\TestCase;
use Silex\Application;
use Skel\ProjectProvider;
use Skel\View\Service;
use Skel\ViewProvider;

class ViewProviderTest extends TestCase
{
    public function testRegister()
    {
        $app = new Application([
            'root.path' => TEST_ROOT_PATH.'/Fixtures'
        ]);

        $app->register(new ViewProvider);

        $this->assertArrayHasKey('view.path',       $app);
        $this->assertArrayHasKey('twig.path',       $app);
        $this->assertArrayHasKey('view.function',   $app);
        $this->assertArrayHasKey('view.filter',     $app);
        $this->assertArrayHasKey('twig.cache.path', $app);
        $this->assertArrayHasKey('twig.options',    $app);
        $this->assertArrayHasKey('view',            $app);

        $this->assertTrue($app['twig.path']     instanceof \ArrayObject);
        $this->assertTrue($app['view.function'] instanceof \ArrayObject);
        $this->assertTrue($app['view.filter']   instanceof \ArrayObject);
        $this->assertTrue($app['view']          instanceof Service);

        $this->assertEquals(
            TEST_ROOT_PATH.'/Fixtures/view',
            $app['twig.path'][0]
        );

        $this->assertEquals(
            TEST_ROOT_PATH.'/Fixtures/view/tmp',
            $app['twig.cache.path']
        );
    }

    public function testRegisterWithProjectProvider()
    {
        $app = new Application();
        $app->register(new ProjectProvider);
        $app->register(new ViewProvider);

        $this->assertEquals(
            realpath(TEST_ROOT_PATH.'/..').'/view',
            $app['twig.path'][0]
        );

        $this->assertEquals(
            realpath(TEST_ROOT_PATH.'/..').'/tmp/view',
            $app['twig.cache.path']
        );
    }
}