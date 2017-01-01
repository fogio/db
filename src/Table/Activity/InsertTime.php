<?php

namespace Fogio\Db\Table\Activity;

use Fogio\Middleware\Process;

class InsertTime
{
    protected $field;

    public function setField($field)
    {
        $this->field = $field;
    }

    public function onInsert(Process $process)
    {
        $field = $this->getField($process);
        if (!array_key_exists($field, $process->row)) {
            $process->row[$field] = time();
        }
        $process();
    }

    public function onInsertAll(Process $process)
    {
        $time = time();
        foreach ($process->rows as $k => $row) {
            if (!array_key_exists($field, $row)) {
                $process->rows[$k][$field] = $time;
            }
        } 
        $process();
    }

    protected function getField(Process $process)
    {
        if ($this->field === null) {
            $this->field = "{$process->table->getName()}_insert";
        }

        return $this->field;
    }

}
