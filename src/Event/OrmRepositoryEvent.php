<?php

namespace Skel\Event;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\Event;

class OrmRepositoryEvent extends Event
{
    private $repository;

    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }
}