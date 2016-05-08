<?php

namespace Skel;

use Symfony\Component\HttpFoundation\Request;

trait ConsoleApplicationTrait
{
    public function command($definition, $action)
    {
        return $this['controllers']->command($definition, $action);
    }

    public function run(Request $req = null)
    {
        if($this['console.env']){
       		return $this['console.run']();
        }

        parent::run($req);
    }
}