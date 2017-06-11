<?php

namespace Skel\Application;

trait ViewTrait
{
    public function html($template, array $data = [])
    {
        return $this['view']->response($template, $data);
    }

    public function render($template, array $data = [])
    {
        return $this['view']->render($template, $data);
    }

    public function viewFunction($name, $callback)
    {
        return $this['view.function'][$name] = $callback;
    }

    public function viewFilter($name, $callback)
    {
        return $this['view.filter'][$name] = $callback;
    }
}