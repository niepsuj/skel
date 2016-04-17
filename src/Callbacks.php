<?php

namespace Skel;

use Silex\CallbackResolver;
use Pimple\Container;

class Callbacks extends CallbackResolver{

    private $app;

    public function __construct(Container $app)
    {
        $this->app = $app;
        parent::__construct($app);
    }

    public function isValid($name) {
        if(is_string($name)){
            return parent::isValid($name) || (isset($this->app[$name]) && is_callable($this->app[$name]));
        }

        return false;
    }

    public function convertCallback($name) {      
        if(isset($this->app[$name])){
            $callback = $this->app[$name];
            if(is_callable($callback)){
                return $callback;
            }
        }

        return parent::convertCallback($name);
    }
}  