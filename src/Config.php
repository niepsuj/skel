<?php

namespace Skel;

class Config implements \ArrayAccess
{
    protected $data;
    protected $field;
    protected $parent;

    public function __construct($data, $field, $parent)
    {
        $this->data = $data;
        $this->field = $field;
        $this->parent = $parent;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        if(is_array($this->data[$offset])){
            return new Config($this->data[$offset], $offset, $this);
        }

        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
        if(null !== $this->parent){
            $this->parent[$this->field] = $this->data;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
        if(null !== $this->parent){
            $this->parent[$this->field] = $this->data;
        }
    }
}