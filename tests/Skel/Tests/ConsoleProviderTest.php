<?php

namespace Skel\Tests;

use PHPUnit\Framework\TestCase;
use Silex\Application;
use Skel\Console\ControllerCollection;
use Skel\Console\Helper;
use Skel\ConsoleProvider;
use Symfony\Component\Console\Application as ConsoleApplication;

class ConsoleProviderTest extends TestCase
{
    public function testRegister()
    {
        $app = new Application([
            'name' => 'test',
            'version' => 1
        ]);
        $app->register(new ConsoleProvider);

        $this->assertArrayHasKey('console.env', $app);
        $this->assertEquals(PHP_SAPI === 'cli', $app['console.env']);

        $this->assertArrayHasKey('console.app', $app);
        $this->assertTrue($app['console.app'] instanceof ConsoleApplication);

        $this->assertArrayHasKey('command_class', $app);
        $this->assertEquals('Skel\\Console\\Command', $app['command_class']);

        $this->assertTrue($app['controllers_factory'] instanceof ControllerCollection);

        $this->assertArrayHasKey('console', $app);
        $this->assertTrue($app['console'] instanceof Helper);
    }
}