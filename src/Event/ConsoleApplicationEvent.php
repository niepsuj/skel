<?php

namespace Skel\Event;

use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\Event as BaseEvent;

class ConsoleApplicationEvent extends BaseEvent
{
    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param Application $application
     * @return ConsoleApplicationEvent
     */
    public function setApplication($application)
    {
        $this->application = $application;
        return $this;
    }


}