<?php

namespace Fogio\Db\Table;

trait TableAwareTrait
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * Sets the Table.
     *
     * @param Table $table A Table instance
     */
    public function setTable(Table $table)
    {
        $this->table = $table;
    }
}
