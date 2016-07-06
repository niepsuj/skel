<?php

namespace Skel;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Helper\QuestionHelper;

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

		$app->on('console.command', function(ConsoleCommandEvent $event) use ($app){
			$app['console.input']   = $event->getInput();
			$app['console.output']  = $event->getOutput();
		});

		$app['console'] = function($app){
			return new ConsoleHelper(
				$app['console.input'],
				$app['console.output']
			);
		};
	}
} 