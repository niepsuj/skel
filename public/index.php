<?php

require_once '../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app->register(new Skel\ProjectProvider, 	[ ] );
$app->register(new Skel\ViewProvider, 		[ 'view.path' => __DIR__.'/views' ] );

$app->get('/', function() use ($app){
	var_dump($app['env']);
	return $app['view']('index', ['var' => 'value']);
});

$app->run();