<?php

namespace Fogio\Db\Table\Activity;

use Fogio\Middleware\Process;

class SerializeFields
{
    protected $fields;

    public function setFields($fields)
    {
        $this->fields = $fields;
    
        return $this;
    }

    public function onFetch(Process $process)
    {
        $process();

        if (!is_array($process->result)) {
            return;
        }

        $process->result = $this->decodeRow($process->result);
    }    

    public function onFetchAll(Process $process)
    {
        $process();

        $process->result = $this->decodeRows($process->result);
    }
    
    public function onInsert(Process $process)
    {
        $process->row = $this->encodeRow($$process->row);

        $process();
    }

    public function onInsertAllPre(Process $process)
    {
        $process->rows = $this->encodeRows($process->rows);

        $process();
    }

    public function onUpdate(Process $process)
    {
        $process->data = $this->encodeRow($process->data);

        $process();
    }

    protected function encode($notScalar)
    {
        return json_encode($notScalar);
    }

    protected function decode($scalar)
    {
        return json_decode($scalar);
        
    }

    protected function encodeRow($row)
    {
        foreach ($this->fields as $field) {
            if (array_key_exists($field, $row) && !is_scalar($row[$field])) {
                $row[$field] = $this->encode($row[$field]);
            }
        }

        return $row; 
    }
    
    protected function decodeRow($row)
    {
        foreach ($this->fields as $field) {
            if (array_key_exists($field, $row) && is_scalar($row[$field])) {
                $row[$field] = $this->decode($row[$field]);
            }
        }

        return $row; 
    }

    protected function encodeRows($rows)
    {
        foreach ($rows as $k => $row) {
            $rows[$k] = $this->encodeRow($row);
        }

        return $rows;
    }
    
    protected function decodeRows($rows)
    {
        foreach ($rows as $k => $row) {
            $rows[$k] = $this->decodeRow($row);
        }

        return $rows;
    }
    
}
