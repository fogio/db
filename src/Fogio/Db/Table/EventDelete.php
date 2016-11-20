<?php

namespace Fogio\Db\Table;

class EventDelete extends Event
{
    public $id = 'Delete';
    public $fdq;

    public function __construct($fdq)
    {
        $this->fdq = $fdq;
    }

}
