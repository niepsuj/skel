<?php

namespace Skel;

use Silex\Controller;
use Silex\ControllerCollection;
use Silex\Route;

class ConsoleControllerCollection extends ControllerCollection
{
	protected $commands = [];
	protected $commandClass = '';

	public function __construct(Route $defaultRoute, $routesFactory = null, $commandClass)
    {
    	parent::__construct($defaultRoute, $routesFactory);
    	$this->commandClass = $commandClass;
    }

	public function command($definition, $action)
	{
		$commandClass = $this->commandClass;
		$command = new $commandClass($definition);
		$command->setCode($action);

		$this->commands[] = $command;
		return $command;
	}

	public function flushCommands($prefix = '')
	{
		$commands = [];
		foreach ($this->controllers as $controller) {
            if (!($controller instanceof Controller)) {
                $commands = array_merge($commands, $controller->flushCommands($controller->prefix));
            }
        }

        foreach($this->commands as $command){
            $commands[] = $command->addPrefix($prefix)->boot();
        }

        return $commands;
	}
}