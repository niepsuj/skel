<?php

namespace Skel;

use MongoDB\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Skel\Event\MongoDBEvent;

class MongoProvider implements ServiceProviderInterface{

    public function register(Container $app)
    {
        $app['mongo.db'] = function($app){
            return preg_replace('/_+/', '_',
                preg_replace(
                    '/[^a-z0-9_]/', '_',
                    strtolower($app['name'])
                )
            );
        };

        $app['mongo.server'] = 'mongodb://127.0.0.1/';
        $app['mongo.client'] = function($app){
            return new Client($app['mongo.server']);
        };

        $app['mongo'] = function($app){
            return $app['dispatcher']->dispatch(
                ProjectEvents::MONGO_CONNECTED,
                new MongoDBEvent(
                    $app['mongo.client']->selectDatabase($app['mongo.db'])
                )
            )->getMongoDB();
        };
    }
}