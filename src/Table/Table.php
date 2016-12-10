<?php

namespace Fogio\Db\Table;

use Fogio\Db\Db;
use Fogio\Container\ContainerTrait;
use Fogio\Util\MiddlewareProcess as Process;

class Table
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
    
    public function setExtensions($extensions)
    {
        // clean caches
        unset(
            $this->_extFetch, $this->_extFetchAll,
            $this->_extInsert, $this->_extInsertAll,
            $this->_extUpdate, $this->_extDelete,
            $this->_init // for ExtendTableInterface
        );

        $this->_ext = $extensions;
        
        return $this;
    }

    public function getExtensions()
    {
        return $this->_ext;
    }

    public function setLinks($links)
    {
        $this->_links = $links;
    
        return $this;
    }
    
    public function getLinks()
    {
        return $this->_links;
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
    
    protected function provideLinks() 
    {
        return [];
    }

    /* read */
    
    public function getFetcher()
    {
        return [':select' => $this->_fields, ':from' => $this->_name];
    }

    public function fetch($fdq)
    {
        return (new Process($this->_extFetch, 'onFetch', ['table' => $this, 'query' => $fdq + $this->getFetcher()]))->__invoke()->result;
    }

    public function fetchAll($fdq)
    {
        return (new Process($this->_extFetchAll, 'onFetchAll', ['table' => $this, 'query' => $fdq + $this->getFetcher()]))->__invoke()->result;
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
        return (new Process($this->_extInsert, 'onInsert', ['table' => $this, 'row' => $row]))->__invoke()->result;
    }

    public function insertAll(array $rows)
    {
        return (new Process($this->_extInsertAll, 'onInsertAll', ['table' => $this, 'rows' => $rows]))->__invoke()->result;
    }

    public function update(array $data, array $fdq)
    {
        return (new Process($this->_extUpdate, 'onUpdate', ['table' => $this, 'data' => $data, 'query' => $fdq]))->__invoke()->result;
    }

    public function delete(array $fdq)
    {
        return (new Process($this->_extDelete, 'onDelete', ['table' => $this, 'query' => $fdq]))->__invoke()->result;
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

    protected function __links()
    {
        return $this->setLinks($this->provideLinks())->getLinks();
    }

    protected function __ext()
    {
        return $this->setExtensions($this->provideExtensions())->getExtensions();
    }

    protected function __extFetch()
    {
        return $this->_extIndex('Fetch', OnFetchInterface::class);
    }

    protected function __extFetchAll()
    {
        return $this->_extIndex('FetchAll', OnFetchAllInterface::class);
    }

    protected function __extInsert()
    {
        return $this->_extIndex('Insert', OnInsertInterface::class);
    }

    protected function __extInsertAll()
    {
        return $this->_extIndex('InsertAll', OnInsertAllInterface::class);
    }

    protected function __extUpdate()
    {
        return $this->_extIndex('Update', OnUpdateInterface::class);
    }

    protected function __extDelete()
    {
        return $this->_extIndex('Delete', OnDeleteInterface::class);
    }

    protected function _extIndex($operation, $interface)
    {
        $index = "_ext$operation"; 
        $this->$index = [];
        foreach ($this->_extension as $extension) {
            if ($extension instanceof $interface) {
                $this->$index[] = $extension;
            }
        }
        $this->$index[] = $this;
        return $this->$index;
    }

    protected function __init()
    {
        foreach ($this->_ext as $extension) {
            if ($extension instanceof OnExtendInterface) {
                $extension->onExtend($this);
            }
        }
    }

}
