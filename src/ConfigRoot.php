<?php

namespace Skel;

class ConfigRoot extends Config
{
    use SaveableTrait;

    public function __construct($data)
    {
        $this->data = $data;
    }

    function flush()
    {
        return $this->data;
    }
}