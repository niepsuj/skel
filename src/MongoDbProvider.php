<?php

namespace Skel;

use Doctrine\MongoDB\Connection;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Skel\Event\MongoDBEvent;

class MongoDbProvider implements ServiceProviderInterface{

    public function register(Container $app)
    {
        if(!isset($app['mongodb.name'])){
            $app['mongodb.name'] = function($app){
                return preg_replace('/_+/', '_',
                    preg_replace(
                        '/[^a-z0-9_]/', '_',
                        strtolower($app['name'])
                    )
                );
            };
        }

        if(!isset($app['mongodb.options'])){
            $app['mongodb.options'] = [];
        }

        if(!isset($app['mongodb.server'])){
            $app['mongodb.server'] = 'mongodb://localhost:27017';
        }

        $app['mongodb.connection'] = function(Container $app){
            return new Connection(
                $app['mongodb.server'],
                $app['mongodb.options']
            );
        };

        $app['mongodb'] = function($app){
            return $app['dispatcher']->dispatch(
                ProjectEvents::MONGO_CONNECTED,
                new MongoDBEvent(
                    $app['mongodb.connection']->selectDatabase($app['mongodb.name'])
                )
            )->getMongoDB();
        };
    }
}