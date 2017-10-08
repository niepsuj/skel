<?php

namespace Skel\Event;

use Doctrine\MongoDB\Database;
use Symfony\Component\EventDispatcher\Event as BaseEvent;

class MongoDBEvent extends BaseEvent
{
    protected $mongoDB;

    public function __construct(Database $mongoDB)
    {
        $this->mongoDB = $mongoDB;
    }

    /**
     * @return Database
     */
    public function getMongoDB()
    {
        return $this->mongoDB;
    }

    /**
     * @param Database $mongoDB
     * @return \MongoDB
     */
    public function setMongoDB($mongoDB)
    {
        $this->mongoDB = $mongoDB;
        return $this;
    }
}