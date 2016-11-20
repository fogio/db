<?php

namespace Fogio\Db\Table;

class EventInsert extends Event
{
    public $id = 'Insert';
    public $row;

    public function __construct($row)
    {
        $this->row = $row;
    }
}
