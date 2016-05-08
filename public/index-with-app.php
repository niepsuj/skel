<?php

require_once '../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Response;

$app = new Skel\Test\Application();
$app->register(new Skel\ProjectProvider, 	[ 'env' => getenv('APPLICATION_ENV') ] );
$app->register(new Skel\ViewProvider, 		[ 'view.path' => __DIR__.'/views' ] );

$app->get('/', function() use ($app){
	return $app->html('index');
});

$app->run();