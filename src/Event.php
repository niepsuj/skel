<?php

namespace Skel;

use Symfony\Component\EventDispatcher\Event as BaseEvent;

class Event extends BaseEvent implements \ArrayAccess
{
	public function __construct($data)
	{
		if(is_array($data)){
			$this->merge($data);
		}
	}
}