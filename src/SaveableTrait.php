<?php

namespace Skel;

trait SaveableTrait
{
    protected $saveCallback = null;

    public function setSaveCallback(callable $save)
    {
        $this->saveCallback = $save;
    }

    public function save()
    {
        if(null === $this->saveCallback){
            throw new \Exception('Missing save callback');
        }

        return call_user_func($this->saveCallback, $this);
    }

    abstract function flush();
}