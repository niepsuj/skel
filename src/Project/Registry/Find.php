<?php

namespace Skel\Project\Registry;

use Skel\Project\Registry;

class Find
{
    protected $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
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
        } elseif (substr($name, 0, 7) === 'countBy') {
            $query[strtolower(substr($name, 7))] = $args[0];
            return $this->count($query);
        }

        throw new \BadMethodCallException('Invalid method');
    }

    public function count($match, $callback = null)
    {
        $count = 0;

        if (is_callable($match)) {
            $callback = $match;
            $match = null;
        }

        foreach ($this->registry as $id => $row) {
            $test = $this->test($match, $row);


            if ($test) {
                if (null !== $callback) {
                    if (true === call_user_func($callback, $row)) {
                        $count++;
                    }
                } else {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function find($match, callable $callback = null)
    {
        $matched = [];

        if (is_callable($match)) {
            $callback = $match;
            $match = null;
        }

        foreach ($this->registry as $id => $row) {
            $test = $this->test($match, $row);

            if ($test) {
                if (null !== $callback) {
                    if (true === call_user_func($callback, $row)) {
                        $matched[] = $row;
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
            $test = $this->test($match, $row);

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

    private function test($match, $row)
    {
        $test = true;
        if(null !== $match){
            foreach ($match as $key => $value) {
                $test = $test && isset($row[$key]) && $row[$key] == $value;
            }
        }

        return $test;
    }
}