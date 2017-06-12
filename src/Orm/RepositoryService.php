<?php

namespace Skel\Orm;

use Doctrine\ORM\EntityManager;
use Skel\Event\OrmRepositoryEvent;
use Skel\ProjectEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;

class RepositoryService
{
    private $em;
    private $dispatcher;

    public function __construct(EntityManager $em, EventDispatcher $dispatcher)
    {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
    }

    public function __get($name)
    {
        return $this->dispatcher
            ->dispatch(
                ProjectEvents::ORM_GET_REPOSITORY,
                new OrmRepositoryEvent(
                    $this->em->getRepository('Entity\\'.ucfirst($name))
                )
            )->getRepository();
    }

    public function __call($name, $args)
    {
        if(substr($name, 0, 3) == 'get'){
            return $this->__get(lcfirst(substr($name, 3)));
        }

        return $this->__get($name);
    }
}