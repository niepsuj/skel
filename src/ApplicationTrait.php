<?php

namespace Skel;

use Symfony\Component\EventDispatcher\Event as DispatcherEvent;

trait ApplicationTrait
{

	public function html($template, $data = null)
	{
		return $this['view']($template, $data);
	}

	public function trigger($name, $data = null)
    {
        if(!$data instanceof DispatcherEvent){
            $data = new Event($data);
        }

        return $this['dispatcher']->dispatch($name, $data);
    }

    public function addFunction($name, $callback)
    {
        return $this['view.function']($name, $callback);
    }

    public function addFilter($name, $callback)
    {
        return $this['view.filter']($name, $callback);
    }

    public function config($name, $flatten = false, $saveable = false)
    {
    	return $this['config.load']($name, $flatten, $saveable);
    }
}