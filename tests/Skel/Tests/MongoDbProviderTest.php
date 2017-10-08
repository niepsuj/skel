<?php

namespace Skel\Tests;

use Doctrine\MongoDB\Connection;
use Doctrine\MongoDb\Database;
use PHPUnit\Framework\TestCase;
use Silex\Application;
use Skel\Event\MongoDBEvent;
use Skel\MongoDbProvider;
use Skel\ProjectEvents;

class MongoDbProviderTest extends TestCase
{
    public function testRegister()
    {
        $app = new Application([
            'name' => 'test'
        ]);
        $app->register(new MongoDbProvider);

        $this->assertArrayHasKey('mongodb.name', $app);
        $this->assertEquals('test', $app['mongodb.name']);

        $this->assertArrayHasKey('mongodb.server', $app);
        $this->assertEquals('mongodb://localhost:27017', $app['mongodb.server']);

        $this->assertArrayHasKey('mongodb.connection', $app);
        $this->assertTrue($app['mongodb.connection'] instanceof Connection);

        $triggerd = false;
        $app->on(ProjectEvents::MONGO_CONNECTED, function(MongoDBEvent $event) use (&$triggerd){
             $triggerd = true;
        });

        $this->assertTrue($app['mongodb'] instanceof Database);
        $this->assertTrue($triggerd);
    }
}