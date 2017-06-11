<?php

namespace Skel\Tests;

use PHPUnit\Framework\TestCase;
use Silex\Application;
use Skel\Project\Callbacks;
use Skel\Project\StoreEvent;
use Skel\Project\Registry;
use Skel\Project\Store;
use Skel\ProjectProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectProviderTest extends TestCase
{
    public function testRegister()
    {
        $app = new Application();
        $app->register(new ProjectProvider);

        $this->assertArrayHasKey('env.name', $app);
        $this->assertArrayHasKey('env', $app);
        $this->assertArrayHasKey('debug', $app);
        $this->assertArrayHasKey('root.path', $app);
        $this->assertArrayHasKey('tmp.path', $app);
        $this->assertArrayHasKey('src.path', $app);
        $this->assertArrayHasKey('vendor.path', $app);
        $this->assertArrayHasKey('public.path', $app);
        $this->assertArrayHasKey('log.path', $app);
        $this->assertArrayHasKey('composer', $app);
        $this->assertArrayHasKey('name', $app);
        $this->assertArrayHasKey('version', $app);
        $this->assertArrayHasKey('registry', $app);
        $this->assertArrayHasKey('store', $app);
    }

    public function testRegisterEnv()
    {
        putenv('ENV=stage');
        $app = new Application();
        $app->register(new ProjectProvider, [
            'env.name' => 'ENV'
        ]);

        $this->assertEquals('stage', $app['env']);
    }

    public function testRegisterDebug()
    {
        putenv('ENV=stage');
        $app = new Application();
        $app->register(new ProjectProvider, [
            'env.name' => 'ENV'
        ]);

        $this->assertTrue($app['debug']);
    }

    public function testRegisterPaths()
    {
        $app = new Application();
        $app->register(new ProjectProvider);
        $this->assertEquals(
            realpath(TEST_ROOT_PATH.'/..'),
            $app['root.path']
        );
        $this->assertEquals(
            realpath(TEST_ROOT_PATH.'/..').'/tmp',
            $app['tmp.path']
        );
        $this->assertEquals(
            realpath(TEST_ROOT_PATH.'/..').'/src',
            $app['src.path']
        );
        $this->assertEquals(
            realpath(TEST_ROOT_PATH.'/..').'/vendor',
            $app['vendor.path']
        );
        $this->assertEquals(
            realpath(TEST_ROOT_PATH.'/..').'/log',
            $app['log.path']
        );
        $this->assertEquals(
            realpath(TEST_ROOT_PATH.'/..').'/public',
            $app['public.path']
        );
    }

    public function testRegisterRootPathOverwrite()
    {
        $app = new Application([
            'root.path' => TEST_ROOT_PATH.'/Fixtures'
        ]);
        $app->register(new ProjectProvider);
        $this->assertEquals(
            TEST_ROOT_PATH.'/Fixtures',
            $app['root.path']
        );
    }

    public function testRegisterComposer()
    {
        $app = new Application();
        $app->register(new ProjectProvider);
        $this->assertEquals('niepsuj/skel', $app['composer']['name']);

        $data = json_decode(file_get_contents(
            TEST_ROOT_PATH.'/Fixtures/composer.json'
        ), true);

        $app = new Application([
            'root.path' => TEST_ROOT_PATH.'/Fixtures'
        ]);
        $app->register(new ProjectProvider);
        $this->assertEquals($data, $app['composer']);
    }

    public function testRegisterAppName()
    {
        $app = new Application();
        $app->register(new ProjectProvider);
        $this->assertEquals('niepsuj.skel', $app['name']);

        $app = new Application([
            'root.path' => TEST_ROOT_PATH.'/Fixtures'
        ]);
        $app->register(new ProjectProvider);
        $this->assertEquals('some.application', $app['name']);
    }

    public function testRegisterVersion()
    {
        $app = new Application([
            'root.path' => TEST_ROOT_PATH.'/Fixtures'
        ]);
        $app->register(new ProjectProvider);
        $this->assertEquals('1.0.0', $app['version']);
    }

    public function testRegisterJsonRequest()
    {
        $app = new Application();
        $app->register(new ProjectProvider);

        $app->get('/', function(Request $req){
            return $req->get('test');
        });

        /** @var Response $response */
        $response = $app->handle(
            Request::create('/', 'GET', [], [], [], ['CONTENT_TYPE' => 'application/json'], '{"test":1}')
        );

        $this->assertEquals('1', $response->getContent());
    }

    public function testRegisterRegistry()
    {
        $app = new Application();
        $app->register(new ProjectProvider);

        $this->assertTrue($app['registry'] instanceof Registry);
    }

    public function testRegisterStore()
    {
        $app = new Application();
        $app->register(new ProjectProvider);

        $this->assertTrue($app['store'] instanceof Store);
    }
}