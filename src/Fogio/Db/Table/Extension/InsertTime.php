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

    public function onInsertPre(EventInsert $event)
    {
        if (array_key_exists($this->field, $row)) {
            return;
        }
        $row[$this->field] = time();
    }

    public function onInsertPost(array &$row, array &$event)
    {
    }

    public function onInsertAllPre(array &$rows, array &$event)
    {
        $time = time();
        foreach ($rows as $k => $row) {
            if (!array_key_exists($this->field, $row)) {
                $rows[$k][$this->field] = $time;
            }
        } 
    }

    public function onInsertAllPost(array &$rows, array &$event)
    {
    }
}
