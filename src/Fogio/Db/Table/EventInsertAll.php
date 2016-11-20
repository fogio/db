<?php

namespace Fogio\Db\Table;

class EventInsertAll extends Event
{
    public $id = 'InsertAll';
    public $rows;
    
    public function __construct($rows)
    {
        $this->rows = $rows;
    }
    
}
