<?php

namespace Skel\Project;

class Registry implements \IteratorAggregate, \ArrayAccess, \Countable
{
    use RegistryTrait;
}