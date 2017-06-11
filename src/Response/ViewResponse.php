<?php

namespace Skel\Response;

use Skel\Project\StoreTrait;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class ViewResponse extends BaseResponse implements \ArrayAccess
{
    use StoreTrait;

    protected $view = null;

    public function __construct($content, $status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);
        $this->setView($content);
    }
    
    /**
     * Szablon
     */
    public function setView($name) { 
        $this->view = $name; 
        return $this;       
    }

    public function getView() { 
        return $this->view;                       
    }
}