<?php

namespace Skel\Console;

use Pimple\Container;
use Symfony\Component\Console\Helper\Helper;

class ApplicationHelper extends Helper implements \ArrayAccess
{
    /**
     * @var Container
     */
    protected $application;

    public function __construct(Container $application)
    {
        $this->application = $application;
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function getName()
    {
        return 'application';
    }

    public function offsetExists($offset)
    {
        return $this->application->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->application->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->application->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->application->offsetUnset($offset);
    }
}