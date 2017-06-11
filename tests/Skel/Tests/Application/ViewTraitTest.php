<?php

namespace Skel\Tests\Application;

use PHPUnit\Framework\TestCase;
use Silex\Application;
use Skel\Application\ViewTrait;
use Skel\ProjectProvider;
use Skel\ViewProvider;
use Symfony\Component\HttpFoundation\Request;

class ViewTraitTest extends TestCase
{
    public function testHtml()
    {
        $app = new ViewAppicationTraitTestApplication();
        $app->get('/', function() use ($app){
            return $app->html('index');
        });

        $response = $app->handle(
            Request::create('/', 'GET')
        );

        $this->assertEquals('ok', $response->getContent());
    }

    public function testRender()
    {
        $app = new ViewAppicationTraitTestApplication();
        $app->get('/', function() use ($app){
            return $app->html('index');
        });

        $this->assertEquals('ok', $app->render('index'));
    }

    public function testViewFunction()
    {
        $app = new ViewAppicationTraitTestApplication();
        $app->viewFunction('test', function(){ return 'ok'; });
        $app->boot();

        $this->assertEquals('ok', $app->render('function'));
    }

    public function testViewFilter()
    {
        $app = new ViewAppicationTraitTestApplication();
        $app->viewFilter('test', function(){ return 'ok'; });
        $app->boot();

        $this->assertEquals('ok', $app->render('filter'));
    }
}

class ViewAppicationTraitTestApplication extends Application
{
    use ViewTrait;

    public function __construct()
    {
        parent::__construct([
            'root.path' => TEST_ROOT_PATH.'/Fixtures'
        ]);

        $this->register(new ProjectProvider);
        $this->register(new ViewProvider);
    }
}