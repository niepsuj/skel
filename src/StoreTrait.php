<?php

namespace Skel;

trait StoreTrait 
{
	protected $store = [];

	public function offsetSet($name, $value)    { $this->store[$name] = $value;          				}
    public function offsetGet($name)            { return $this->store[$name];            				}
    public function offsetExists($name)         { return isset($this->store[$name]);     				}
    public function offsetUnset($name)          { unset($this->store[$name]);            				}

    public function set($name, $value)          { $this->store[$name] = $value;    	return $this;       }
    public function get($name)                  { return $this->store[$name];                          	}
    public function exists($name)               { return isset($this->store[$name]);         			}
    public function clean($name)                { unset($this->store[$name]); 		return $this;  		}

    public function merge($values = array())    {
        if(is_array($values))
            $this->store = array_merge($this->store, $values);  
        return $this; 
    }
    
    public function flush()                     { return $this->store;                       			}
}