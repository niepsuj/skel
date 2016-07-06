<?php

namespace Skel;

use MongoDB\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MongoProvider implements ServiceProviderInterface{

    public function register(Container $app)
    {
        $app['db.server'] = function($app){
            return new Client($app['mongo.server']);
        };

        $app['db'] = function($app){
            $db = $app['db.server']->selectDatabase($app['mongo.db']);
            $app['trigger']('mongodb.connected', ['db' => $db]);
            return $db;
        };
    }
}