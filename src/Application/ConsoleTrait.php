<?php

namespace Skel\Application;

use Symfony\Component\HttpFoundation\Request;

trait ConsoleTrait
{
    public function command($definition, $action)
    {
        return $this['controllers']->command($definition, $action);
    }

    public function run(Request $req = null)
    {
        if(!$this['console.env']){
            return parent::run($req);
        }

        $this->boot();
        $this['console.app']->addCommands(
            $this['controllers']->flushCommands()
        );
        $this['console.app']->run();
    }
}