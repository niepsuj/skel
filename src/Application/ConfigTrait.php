<?php

namespace Skel\Application;

trait ConfigTrait
{
    public function config($name, $flatten = false, $prefix = false)
    {
        return $this['config']->load($name, $flatten, $prefix);
    }
}