<?php

namespace Skel;

trait ApplicationTrait 
{

	public function html($template, $data = null)
	{
		return $this['view']($template, $data);
	}

	public function trigger($name, $data = null)
    {
        return $app['trigger']($name, $data);
    }

    public function addFunction($name, $callback)
    {
        return $this['view.function']($name, $callback);
    }

    public function addFilter($name, $callback)
    {
        return $this['view.filter']($name, $callback);
    }

    public function config($name, $flatten = false)
    {
    	return $this['config.load']($name, $flatten);
    }
}