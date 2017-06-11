<?php

namespace Skel\Tests\View;

use PHPUnit\Framework\TestCase;
use Silex\Application;
use Skel\ProjectProvider;
use Skel\ViewProvider;

class EnvTokenParserTest extends TestCase
{
    public function testResult()
    {
        $app = new Application([
            'root.path' => TEST_ROOT_PATH.'/Fixtures',
            'env' => 'production'
        ]);
        $app->register(new ProjectProvider);
        $app->register(new ViewProvider);
        $app->boot();

        $result = $app['view']->render('env');
        $this->assertEquals('production', $result);
    }
}