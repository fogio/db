<?php

namespace Fogio\Db\Table;

use Fogio\Db\Db;
use Fogio\Container\ContainerTrait;
use Fogio\Middleware\Process;
use Fogio\Middleware\MiddlewareTrait;

class Table
{
    use ContainerTrait;
    use MiddlewareTrait { setActivities as protected; process as protected; }

    /**
     * @var Db 
     */
    protected $_db;

    /* setup */

    public function setDb(Db $db)
    {
        $this->_db = $db;

        return $this;
    }

    public function getDb()
    {
        return $this->_db;
    }

    public function setName($name)
    {
        $this->_name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setKey($key)
    {
        $this->_key = $key;
    }

    public function getKey()
    {
        return $this->_key;
    }

    public function setFields($fields)
    {
        $this->_fields = $fields;

        return $this;
    }

    public function getFields()
    {
        return $this->_fields;
    }
    
    public function setExtensions(array $extensions)
    {
        return $this->setActivities(array_merge($extensions, [$this]));
    }

    public function getExtensions()
    {
        return $this->getActivities();
    }

    /* provide */

    protected function provideName() 
    {
        return lcfirst((new \ReflectionClass($this))->getShortName());
    }

    protected function provideKey() 
    {
        return $this->_db->_schema->{$this->_name}->key;
    }

    protected function provideFields() 
    {
        return $this->_db->_schema->{$this->_name}->fields;
    }

    protected function provideExtensions()
    {
        return [];
    }
    
    /* read */
    
    public function fetch($fdq)
    {
        return $this->process('onFetch', [
            'table' => $this,
            'query' => $fdq + [':select' => $this->_fields, ':from' => $this->_name]
        ])->result;
    }

    public function fetchAll($fdq)
    {
        return $this->process('onFetchAll', [
            'table' => $this,
            'query' => $fdq + [':select' => $this->_fields, ':from' => $this->_name]
        ])->result;
    }

    public function fetchCol($fdq, $col = 0)
    {
        $return = [];
        foreach ($this->fetchAll($fdq) as $row) {
            $return[] = $row[$col];
        }

        return $return;
    }

    public function fetchVal($fdq, $col = 0)
    {
        $row = $this->fetch($fdq);

        return $row ? $row[$col] : null;
    }

    public function fetchKeyPair($fdq, $keyColName = 0, $valColName = 1)
    {
        $return = [];
        $intKey = is_int($keyColName);
        $intVal = is_int($valColName);
        $int    = $intKey || $intVal;

        foreach ($this->fetchAll($fdq) as $row) {
            if ($int) {
                $intRow = array_values($row);
            }
            $return[$intKey ? $intRow[$keyColName] : $row[$keyColName]] = $intVal ? $intRow[$valColName] : $row[$valColName];
        }
        return $return;
    }

    public function fetchKeyed($fdq, $colName= 0)
    {
        $return = [];
        $int = is_int($colName);

        foreach ($this->fetchAll($fdq) as $row) {
            $return[$int ? array_values($row)[$colName] : $row[$colName]] = $row;
        }

        return $return;
    }

    public function count($fdq = null, $expr = '*')
    {
        return $this->fetchVal([':select' => "|count($expr)"] + $fdq);
    }

    /* write */

    public function insert(array $row)
    {
        return $this->process('onInsert', ['table' => $this, 'row' => $row])->result;
    }

    public function insertAll(array $rows)
    {
        return $this->process('onInsertAll', ['table' => $this, 'rows' => $rows])->result;
    }

    public function update(array $data, array $fdq)
    {
        return $this->process('onUpdate', ['table' => $this, 'data' => $data, 'query' => $fdq])->result;
    }

    public function delete(array $fdq)
    {
        return $this->process('onDelete', ['table' => $this, 'query' => $fdq])->result;
    }

    /* extension */

    protected function onFetch(Process $process)
    {
        $process->result = $this->getDb()->fetch($process->query);
        $process();
    }

    protected function onFetchAll(Process $process)
    {
        $process->result = $this->getDb()->fetchAll($process->query);
        $process();
    }

    protected function onInsert(Process $process)
    {
        $process->result = $this->getDb()->insert($this->_name, $process->row);
        $process();
    }

    protected function onInsertAll(Process $process)
    {
        $process->result = $this->getDb()->insertAll($this->_name, $process->rows);
        $process();
    }

    protected function onUpdate(Process $process)
    {
        $process->result = $this->getDb()->update($this->_name, $process->data, $process->query);
        $process();
    }

    protected function onDelete(Process $process)
    {
        $process->result = $this->getDb()->delete($this->_name, $process->query);
        $process();
    }

    /* lazy */

    protected function __name()
    {
        return $this->setName($this->provideName())->getName();
    }

    protected function __key()
    {
        return $this->setKey($this->provideKey())->getKey();
    }

    protected function __fields()
    {
        return $this->setFields($this->provideFields())->getFields();
    }

    protected function __init()
    {
        foreach ($this->getActivitiesWithMethod('onExtend') as $extension) {
            $extension->onExtend($this);
        }
    }

}
