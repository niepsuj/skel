<?php

namespace Skel;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class ConsoleProvider implements ServiceProviderInterface
{
	public function register(Container $app)
	{
		$app['console.env'] = PHP_SAPI === 'cli';
		$app['console.app'] = function($app){
			$cliApp = new Application($app['name']);
			$cliApp->setDispatcher($app['dispatcher']);
			return $cliApp;
		};

		$app['command_class'] = 'Skel\\ConsoleCommand';

		$app['controllers_factory'] = $app->factory(function ($app) {
            return new ConsoleControllerCollection($app['route_factory'], $app['routes_factory'], $app['command_class']);
        });

		$app['console.run'] = $app->protect(function() use ($app){
			$app['console.app']->addCommands($app['controllers']->flushCommands());
			$app['console.app']->run();
		});
	}
} 