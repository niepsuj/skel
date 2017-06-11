<?php

namespace Skel\Console;

use Silex\Controller;
use Silex\ControllerCollection as SilexControllerCollection;
use Silex\Route;
use Symfony\Component\Routing\RouteCollection;

class ControllerCollection extends SilexControllerCollection
{

	protected $commands = [];
	protected $commandFactory;

	public function __construct(
	    Route $defaultRoute,
        RouteCollection $routesFactory = null,
        callable $commandFactory = null
    ){
    	parent::__construct($defaultRoute, $routesFactory);
    	$this->commandFactory = $commandFactory;
    }

	public function command($definition, $action)
	{
		$command = call_user_func($this->commandFactory, $definition);
		$command->setCode($action);
		$this->commands[] = $command;

		return $command;
	}

	public function flushCommands($prefix = '')
	{
		$commands = [];
		foreach ($this->controllers as $controller) {
            if (!($controller instanceof Controller)) {
				$commands = array_merge($commands, $controller->flushCommands(
					$prefix.$controller->prefix
				));
            }
        }

        foreach($this->commands as $command){
            $commands[] = $command->addPrefix($prefix)->boot();
        }

        return $commands;
	}
}