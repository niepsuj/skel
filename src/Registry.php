<?php

namespace Skel;

class Registry implements \IteratorAggregate 
{

    protected $registry = array();
    protected $pos = 0;

    public function __construct($data = null)
    {
        if(null === $data) return;
        if(is_array($data)) $this->registry = $data;
        else $this->push($data);
    }

    public function push($value)    { array_push($this->registry, $value); return $this;                                  }
    public function pop()           { return array_pop($this->registry);                                                  }
    public function shift()         { return array_shift($this->registry);                                                }
    public function unshift($value) { array_unshift($this->registry, $value); return $this;                               }
    public function getIterator()   { return new \ArrayIterator($this->registry);                                         }
    public function flush()         { return $this->registry;                                                             }
    public function merge($value)   { $this->registry = array_merge($this->registry, array_values($value)); return $this; }
}