<?php

namespace Fogio\Db\Table;

class EventFetch extends Event
{
    public $id = 'Fetch';
    public $fdq;

    public function __construct($fdq)
    {
        $this->fdq = $fdq;
    }

    
}
