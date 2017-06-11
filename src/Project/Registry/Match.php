<?php

namespace Skel\Project\Registry;

use Skel\Project\Registry;

class Match
{
    protected $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function match($match, $callback = null)
    {
        $registry = $this->registry->registry();
        $remove = [];
        $matched = 0;

        if (is_callable($match)) {
            $callback = $match;
            $match = null;
        }

        foreach ($registry as $id => $row) {

            $test = true;

            if (null !== $match) {
                foreach ($match as $key => $value) {
                    $test = $test && isset($row[$key]) && $row[$key] == $value;
                }
            }

            if ($test) {
                if (null !== $callback) {
                    if (
                        false === $callback ||
                        (
                            is_callable($callback) &&
                            false === ($updated = call_user_func($callback, $row))
                        )
                    ) {
                        $remove[] = $id;
                    } else {
                        if (null !== $updated) {
                            $registry[$id] = $updated;
                        }
                    }
                }

                $matched++;
            }
        }

        foreach ($remove as $id) {
            array_splice($registry, $id, 1);
        }

        $this->registry->set($registry);
        return $matched;
    }
}