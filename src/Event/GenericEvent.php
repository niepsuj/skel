<?php

namespace Skel\Event;

use Skel\Project\StoreTrait;
use Symfony\Component\EventDispatcher\Event as BaseEvent;

class GenericEvent extends BaseEvent implements \ArrayAccess
{
	use StoreTrait;

	public function __construct($data)
	{
		if(is_array($data)){
			$this->merge($data);
		}
	}
}