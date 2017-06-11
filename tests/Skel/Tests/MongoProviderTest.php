<?php

namespace Skel\Tests;

use MongoDB\Client;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;
use Silex\Application;
use Skel\Event\MongoDBEvent;
use Skel\MongoProvider;
use Skel\ProjectEvents;

class MongoProviderTest extends TestCase
{
    public function testRegister()
    {
        $app = new Application([
            'name' => 'test'
        ]);
        $app->register(new MongoProvider);

        $this->assertArrayHasKey('mongo.db', $app);
        $this->assertEquals('test', $app['mongo.db']);

        $this->assertArrayHasKey('mongo.server', $app);
        $this->assertEquals('mongodb://127.0.0.1/', $app['mongo.server']);

        $this->assertArrayHasKey('mongo.client', $app);
        $this->assertTrue($app['mongo.client'] instanceof Client);

        $triggerd = false;
        $app->on(ProjectEvents::MONGO_CONNECTED, function(MongoDBEvent $event) use (&$triggerd){
             $triggerd = true;
        });

        $this->assertTrue($app['mongo'] instanceof Database);
        $this->assertTrue($triggerd);
    }
}