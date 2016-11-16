<?php

namespace Fogio\Db\Table;

use Fogio\Db\Db;
use Fogio\Db\Table\Extension\TableAwareInterface;
use Fogio\Container\ContainerTrait;

class AbstractTable
{

    use ContainerTrait;

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

    public function getName()
    {
        throw new LogicException('getName() not implemented');
    }

    public function getKey()
    {
        return null;
    }

    public function getFields()
    {
        throw new LogicException('getFields() not implemented');
    }
    
    public function getExtensions()
    {

    }

    public function getRelations()
    {
        return [];
    }

    /* read */
    
    public function getFetcher()
    {
        return [':select' => $this->getFields(), ':from' => $this->getName()];
    }

    public function fetch($fdq)
    {
        return $this->_on('Fetch', [$fdq + $this->getFetcher()]);
    }

    public function fetchAll($fdq)
    {
        return $this->_on('FetchAll', [$fdq + $this->getFetcher()]);
    }

    public function fetchCol($fdq)
    {
        return $this->_db->fetchCol($fdq + $this->getFetcher());
    }

    public function fetchVal($fdq)
    {
        return $this->_db->fetchVal($fdq + $this->getFetcher());
    }

    public function fetchKeyPair($fdq)
    {
        return $this->_db->fetchKeyPair($fdq + $this->getFetcher());
    }

    public function fetchKeyed($fdq)
    {
        return $this->_db->fetchKeyed($fdq + $this->getFetcher());
    }

    public function fetchCount($params = null, $expr = '*')
    {
        return $this->_db->fetchCount($fdq + [':from' => $this->getName()], $expr);
    }

    /* write */

    public function insert(array $row)
    {
        return $this->_on('Insert', [$row]);
    }

    public function insertAll(array $rows)
    {
        return $this->_on('InsertAll', [$rows]);
    }

    public function update(array $data, array $fdq)
    {
        return $this->_on('Update', [$data, $fdq]);
    }

    public function delete(array $fdq)
    {
        return $this->_on('Delete', [$fdq]);
    }

    public function save($row)
    {
        $key = $this->getKey();
        
        if ($key === null) {
            throw new LogicException();
        }
        
        if ($row[$key] === null) {
            return $this->insert($row);
        } else {
            $fdq = [$key => $row[$key]];
            unset($row[$key]);
            return $this->update($row, $fdq);
        }
    }

    /* extension */

    protected function on($operation, $args)
    {
        $args[] = []; // add event variable

        foreach ($this->{"_extension$operation"} as $extension) { // pre
            call_user_func_array([$extension, "on{$operation}Pre"], $args);
        }
        
        $args[] = call_user_func_array([$this, "on{$operation}"], $args); // main, add result to args

        foreach ($this->{"_extension$operation"} as $extension) { // pre
            call_user_func_array([$extension, "on{$operation}Post"], $args);
        }

        return end($args);
    }
    protected function onFetch(array &$fdq, array &$event)
    {
        return $this->getDb()->fetch($fdq);
    }

    protected function onFetchAll(array &$fdq, array &$event)
    {
        return $this->getDb()->fetchAll($fdq);
    }

    protected function onInsert(array &$row, array &$event)
    {
        return $this->getDb()->insert($this->getName(), $row);
    }

    protected function onInsertAll(array &$rows, array &$event)
    {
        return $this->getDb()->insertAll($this->getName(), $rows);
    }

    protected function onUpdate(array &$data, array &$fdq, array &$event)
    {
        return $this->getDb()->update($this->getName(), $data, $fdq);
    }

    protected function onDelete(array &$fdq, array &$event)
    {
        return $this->getDb()->delete($this->getName(), $fdq);
    }

    protected function extensionResolve($operation, $interface)
    {
        $resolve = "_extension$operation"; 
        $this->$resolve = [];
        foreach ($this->_extension as $extension) { // $this->_extension - using container service
            if ($extension instanceof $interface) {
                $this->$resolve[] = $extension;
            }
        }
        return $this->$resolve;
    }
 
    protected function __extension()
    {
        $this->_extension = $this->getExtensions();
        foreach ($this->_extension as $extension) {
            if ($extension instanceof TableAwareInterface) {
                $extension->setTable($this);
            }
        }
        return $this->_extension;
    }

    protected function __extensionFetch()
    {
        return $this->extensionResolve('Fetch', OnFetchInterface::class);
    }

    protected function __extensionFetchAll()
    {
        return $this->extensionResolve('FetchAll', OnFetchAllInterface::class);
    }

    protected function __extensionInsert()
    {
        return $this->extensionResolve('Insert', OnInsertInterface::class);
    }

    protected function __extensionInsertAll()
    {
        return $this->extensionResolve('InsertAll', OnInsertAllInterface::class);
    }

    protected function __extensionUpdate()
    {
        return $this->extensionResolve('Update', OnUpdateInterface::class);
    }

    protected function __extensionDelete()
    {
        return $this->extensionResolve('Delete', OnDeleteInterface::class);
    }

    protected function __init()
    {
        foreach ($this->_extension as $extension) {
            if ($extension instanceof OnExtendInterface) {
                $extension->onExtend($this);
            }
        }
    }

}
