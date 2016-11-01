<?php

namespace \Fogio\Db;

use Fogio\Paging\PagingInterface;

class Model
{

    public function addLink($to, $from = null, $join = 'JOIN', $fields = true)
    {
        return $this->_db->_linker->addLink($this, $to, $from, $join, $fields);
    }

}
