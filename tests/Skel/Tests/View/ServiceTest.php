<?php

namespace Skel\Tests\View;

use PHPUnit\Framework\TestCase;
use Silex\Application;
use Skel\ProjectProvider;
use Skel\Response\ViewResponse;
use Skel\ViewProvider;

class ServiceTest extends TestCase
{
    public function testRender()
    {
        $app = new ViewServiceTestAppication();
        $result = $app['view']->render('index');

        $this->assertEquals('ok', $result);
    }

    public function testResponse()
    {
        $app = new ViewServiceTestAppication();
        $result = $app['view']->response('index');
        $this->assertTrue($result instanceof ViewResponse);
        $this->assertEquals('index', $result->getView());
    }

    public function testCleanup()
    {
        $app = new ViewServiceTestAppication();
        $app['view']->render('index');

        $this->assertTrue($app['view']->cleanup());
    }
}

class ViewServiceTestAppication extends Application
{
    public function __construct()
    {
        parent::__construct([
            'root.path' => TEST_ROOT_PATH.'/Fixtures'
        ]);

        $this->register(new ProjectProvider);
        $this->register(new ViewProvider);
    }
}