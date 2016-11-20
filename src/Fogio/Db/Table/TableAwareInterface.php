<?php

namespace Fogio\Db\Table;

use Fogio\Db\Table\Table;

interface TableAwareInterface
{
    /**
     * Sets the Table
     *
     * @param Table
     */
    public function setTable(Table $table);
}
