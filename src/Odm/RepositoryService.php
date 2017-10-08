<?php

namespace Skel\Odm;

use Doctrine\ODM\MongoDB\DocumentManager;
use Event\OdmRepositoryEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class RepositoryService
{
    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    public function __construct(DocumentManager $dm, EventDispatcher $dispatcher)
    {
        $this->dm = $dm;
        $this->dispatcher = $dispatcher;
    }

    public function __get($name){
        return $this->dispatcher->dispatch(
            'odm.get.repository',
            new OdmRepositoryEvent(
                $this->dm->getRepository('Document\\'.ucfirst($name))
            )
        )->getRepository();
    }
}