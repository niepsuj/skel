<?php

namespace Skel\Project;

trait RegistryTrait
{
    protected $registry = [];

    public function push($row)      { array_push($this->registry, $row); return $this;              }
    public function pop()           { return array_pop($this->registry);                            }
    public function shift()         { return array_shift($this->registry);                          }
    public function unshift($row)   { array_unshift($this->registry, $row); return $this;           }

    public function count()         { return count($this->registry);                                }
    public function registry()      { return $this->registry;                                       }
    public function getIterator()   { return new \ArrayIterator($this->registry);                   }

    public function set(array $data = [])       {
        $this->registry = array_values($data);
        return $this;
    }

    public function merge(array $data)          {
        $this->registry = array_merge(
            $this->registry,
            array_values($data)
        );

        return $this;
    }

    public function offsetExists($offset)           { return isset($this->registry[$offset]);       }
    public function offsetSet($offset, $value)      {
        if(null === $offset){
            $this->push($value);
        }else{
            if(!isset($this->registry[$offset])){
                throw new \OutOfBoundsException('Invalid offset');
            }
        }
        $this->registry[$offset] = $value;
    }
    public function offsetGet($offset)              {
        if(!isset($this->registry[$offset])){
            throw new \OutOfBoundsException('Invalid offset');
        }
        return $this->registry[$offset];
    }
    public function offsetUnset($offset)            {
        if(!isset($this->registry[$offset])){
            throw new \OutOfBoundsException('Invalid offset');
        }

        array_splice($this->registry, $offset, 1);
    }
}