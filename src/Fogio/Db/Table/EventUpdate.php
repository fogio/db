<?php

namespace Fogio\Db\Table;

class EventUpdate extends Event
{
    public $id = 'Update';
    public $data;
    public $fdq;

    public function __construct($data, $fdq)
    {
        $this->data = $data;
        $this->fdq = $fdq;
    }
}
