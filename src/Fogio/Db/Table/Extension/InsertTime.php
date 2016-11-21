<?php

namespace Fogio\Db\Table\Extension;

use Fogio\Db\Table\Table;

class InsertTime implements OnInsertInterface, OnInsertAllInterface, TableAwareInterface
{
    use TableAwareTrait;

    protected $field;

    public function setField($field)
    {
        $this->field = $field;
    }

    public function getField()
    {
        if ($this->field === null) {
            $this->field = "{$this->table->getName()}_insert";
        }

        return $this->field;
    }

    public function onInsert(Process $process)
    {
        if (array_key_exists($this->field, $process->row)) {
            return;
        }
        $process->row[$this->field] = time();
        $process();
    }

    public function onInsertAll(Process $process)
    {
        $time = time();
        foreach ($process->rows as $k => $row) {
            if (!array_key_exists($this->field, $row)) {
                $process->rows[$k][$this->field] = $time;
            }
        } 
        $process();
    }

}
