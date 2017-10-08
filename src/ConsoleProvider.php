<?php

namespace Skel;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Skel\Console\ControllerCollection;
use Skel\Console\Helper;
use Skel\Event\CleanupEvent;
use Skel\Event\ConsoleApplicationEvent;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

class ConsoleProvider implements ServiceProviderInterface, BootableProviderInterface
{
	public function register(Container $app)
	{
	    if(!isset($app['name']) || !isset($app['version'])){
	        throw new \Exception('Missing application name or/and version');
        }

		$app['console.env'] = PHP_SAPI === 'cli';
		$app['console.app'] = function($app){
			$cliApp = new Application(
			    $app['name'],
                $app['version']
            );
			$cliApp->setDispatcher($app['dispatcher']);
            return $app['dispatcher']->dispatch(
                ProjectEvents::CONSOLE_APPLICATION,
                new ConsoleApplicationEvent($cliApp)
            )->getApplication();
		};

		$app['command_class'] = 'Skel\\Console\\Command';

		$app['controllers_factory'] = $app->factory(function ($app) {
		    return new ControllerCollection(
                $app['route_factory'],
                $app['callback_resolver'],
                $app['routes_factory'],
                function($definition) use ($app){
                    $commandClass = $app['command_class'];
                    return new $commandClass($definition);
                }
            );
        });

		$app['console'] = function($app){
			return new Helper();
		};
	}

    public function boot(\Silex\Application $app)
    {
        if($app['console.env']){
            $app->on(ConsoleEvents::COMMAND, function(ConsoleCommandEvent $event) use ($app){
                $app['console']->setInput($event->getInput());
                $app['console']->setOutput($event->getOutput());
            });

            if(isset($app['cleanup.scope'])) {
                $app['controllers']->command('cleanup {scope[]}', function ($scope) use ($app) {
                    $app['dispatcher']->dispatch(
                        ProjectEvents::CLEANUP,
                        new CleanupEvent($scope)
                    );
                })
                    ->description('Clear temporary files')
                    ->help('Available scopes: ' . implode(',', (array) $app['cleanup.scope']));
            }
        }
    }
}