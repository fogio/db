<?php

namespace Fogio\Db\Table\Extension;

use Fogio\Db\Table\Table;
use Fogio\Db\Db;

class SerializeFields implements 
    OnFetchInterface, OnFetchAllInterface, 
    OnInsertInterface, OnInsertAllInterface,
    OnUpdateInterface
{
    protected $fields;

    public function setFields($fields)
    {
        $this->$fields = $fields;
    
        return $this;
    }

    public function onFetchPre(array &$fdq, array &$event)
    {
    }

    public function onFetchPost(array &$fdq, array &$event)
    {
        if (!is_array($event['result'])) {
            return;
        }

        $event['result'] = $this->decodeRow($event['result']);
    }    

    public function onFetchAllPre(array &$fdq, array &$event)
    {
    }

    public function onFetchAllPost(array &$fdq, array &$event)
    {
        $event['result'] = $this->decodeRows($event['result']);
    }
    
    public function onInsertPre(array &$row, array &$event)
    {
        $row = $this->encodeRow($row);
    }

    public function onInsertPost(array &$row, array &$event)
    {
    }

    public function onInsertAllPre(array &$rows, array &$event)
    {
        $row = $this->encodeRows($row);
    }

    public function onInsertAllPost(array &$rows, array &$event)
    {
    }

    public function onUpdatePre(array &$data, array &$fdq, array &$event)
    {
        $data = $this->encodeRow($data);
    }

    public function onUpdatePost(array &$data, array &$fdq, array &$event)
    {
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
