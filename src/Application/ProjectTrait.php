<?php

namespace Skel\Application;

use Skel\Event\CleanupEvent;
use Skel\Event\GenericEvent;
use Skel\ProjectEvents;
use Symfony\Component\EventDispatcher\Event as DispatcherEvent;

trait ProjectTrait
{
	public function trigger($name, $data = null)
    {
        if(!$data instanceof DispatcherEvent){
            $data = new GenericEvent($data);
        }

        return $this['dispatcher']->dispatch($name, $data);
    }

    public function cleanup(array $scope = null)
    {
        if(null === $scope){
            $scope = (array) $this['cleanup.scope'];
        }

        return $this['dispatcher']->dispatch(
            ProjectEvents::CLEANUP,
            new CleanupEvent($scope)
        );
    }
}