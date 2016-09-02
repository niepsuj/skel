<?php

namespace Skel;

class Registry implements \IteratorAggregate, \ArrayAccess, \Countable
{
    use SaveableTrait;

    protected $registry = array();
    protected $pos = 0;

    public function __construct($data = null)
    {
        if (null === $data) return;
        if (is_array($data)) $this->registry = $data;
        else $this->push($data);
    }

    public function push($value)
    {
        array_push($this->registry, $value);
        return $this;
    }

    public function pop()
    {
        return array_pop($this->registry);
    }

    public function shift()
    {
        return array_shift($this->registry);
    }

    public function unshift($value)
    {
        array_unshift($this->registry, $value);
        return $this;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->registry);
    }

    public function flush()
    {
        return $this->registry;
    }

    public function merge($value)
    {
        $this->registry = array_merge($this->registry, array_values($value));
        return $this;
    }

    public function count()
    {
        return count($this->registry);
    }

    public function offsetExists($offset)
    {
        return isset($this->registry[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->registry[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->registry[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        array_splice($this->registry, $offset, 1);
    }

    public function match($match, callable $callback = null)
    {
        $remove = [];
        $matched = 0;

        if (is_callable($match)) {
            $callback = $match;
            $match = null;
        }

        foreach ($this->registry as $id => $row) {

            $test = true;

            if (null !== $match) {
                foreach ($match as $key => $value) {
                    $test = $test && isset($row[$key]) && $row[$key] == $value;
                }
            }


            if ($test) {
                if (null !== $callback) {
                    if (false === ($updated = call_user_func($callback, $row))) {
                        $remove[] = $id;
                    } else {
                        if (null !== $updated) {
                            $this->registry[$id] = $updated;
                        }
                    }
                }

                $matched++;
            }
        }

        foreach ($remove as $id) {
            array_splice($this->registry, $id, 1);
        }

        return $matched;
    }

    public function __call($name, $args)
    {
        if (!count($args)) {
            throw new \InvalidArgumentException('Missing query argument');
        }

        $query = [];
        if (substr($name, 0, 6) === 'findBy') {
            $query[strtolower(substr($name, 6))] = $args[0];
            return $this->find($query);
        } elseif (substr($name, 0, 9) === 'findOneBy') {
            $query[strtolower(substr($name, 9))] = $args[0];
            return $this->findOne($query);
        }

        throw new \BadMethodCallException('Invalid method');
    }

    public function find($match, callable $callback = null)
    {
        $matched = [];

        if (is_callable($match)) {
            $callback = $match;
            $match = null;
        }

        foreach ($this->registry as $id => $row) {

            $test = true;

            if (null !== $match) {
                foreach ($match as $key => $value) {
                    $test = $test && isset($row[$key]) && $row[$key] == $value;
                }
            }

            if ($test) {
                if (null !== $callback) {
                    if (true === call_user_func($callback, $row)) {
                        $matched = $row;
                    }
                } else {
                    $matched[] = $row;
                }
            }
        }

        return $matched;
    }

    public function findOne($match, callable $callback = null)
    {
        if (is_callable($match)) {
            $callback = $match;
            $match = null;
        }

        foreach ($this->registry as $id => $row) {
            $test = true;
            if (null !== $match) {
                foreach ($match as $key => $value) {
                    $test = $test && $row[$key] == $value;
                }
            }

            if ($test) {
                if (null !== $callback) {
                    if (true === call_user_func($callback, $row)) {
                        return $row;
                    }
                } else {
                    return $row;
                }
            }
        }

        return null;
    }
}