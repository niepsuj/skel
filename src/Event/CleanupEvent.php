<?php

namespace Skel\Event;

use Symfony\Component\EventDispatcher\Event as BaseEvent;

class CleanupEvent extends BaseEvent implements \IteratorAggregate
{
    protected $scope;

    public function __construct($scope)
    {
        $this->scope = array_combine(
            $scope, array_fill(0, count($scope), [
                'success' => false,
                'message' => ''
            ])
        );
    }

    public function can($scope)
    {
        return isset($this->scope[$scope]);
    }

    public function success($scope)
    {
        return $this->can($scope) && $this->scope[$scope]['success'];
    }

    public function message($scope)
    {
        return $this->can($scope) ? $this->scope[$scope]['message'] : null;
    }

    public function report($scope, $success = true, $message = '')
    {
        $this->scope[$scope] = [
            'success' => true,
            'message' => ''
        ];
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->scope);
    }
}