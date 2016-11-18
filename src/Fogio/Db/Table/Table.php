<?php

namespace Fogio\Db\Table;

use Fogio\Db\Db;
use Fogio\Db\Table\Extension\TableAwareInterface;
use Fogio\Container\ContainerTrait;

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
            $this->_extensionsFetch, $this->_extensionsFetchAll,
            $this->_extensionsInsert, $this->_extensionsInsertAll,
            $this->_extensionsUpdate, $this->_extensionsDelete
        );

        // inject
        foreach ($extensions as $extension) {
            if ($extension instanceof TableAwareInterface) {
                $extension->setTable($this);
            }
        }

        $this->_extensions = $extensions;
        
        return $this;
    }

    public function getExtensions()
    {
        return $this->_extensions;
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
        return null;
    }

    protected function provideKey() 
    {
        return null;
    }

    protected function provideFields() 
    {
        return [];
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
        // middleware
        $event = [];
        $args[] &= $event;
        $extensions &= $this->{"_extensions$operation"};
        $lenght = count($extensions);
        $i = 0;
        // pre
        while ($i < $lenght && !array_key_exists('result', $event)) {
            call_user_func_array([extensions[$i], "on{$operation}Pre"], $args);
            $i++;
        }
        // main 
        if (!array_key_exists('result', $event)) {
            $event['result'] = call_user_func_array([$this, "on{$operation}"], $args);
        }
        // post
        while ($i >= 0 && $i < $lenght) {
            call_user_func_array([extensions[$i], "on{$operation}Post"], $args);
            $i--;
        }

        return $event['result'];
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

    protected function __extensions()
    {
        return $this->setExtensions($this->provideExtensions())->getExtensions();
    }

    protected function __extensionsFetch()
    {
        return $this->_createExtensionsIndex('Fetch', OnFetchInterface::class);
    }

    protected function __extensionsFetchAll()
    {
        return $this->_createExtensionsIndex('FetchAll', OnFetchAllInterface::class);
    }

    protected function __extensionsInsert()
    {
        return $this->_createExtensionsIndex('Insert', OnInsertInterface::class);
    }

    protected function __extensionsInsertAll()
    {
        return $this->_createExtensionsIndex('InsertAll', OnInsertAllInterface::class);
    }

    protected function __extensionsUpdate()
    {
        return $this->_createExtensionsIndex('Update', OnUpdateInterface::class);
    }

    protected function __extensionsDelete()
    {
        return $this->_createExtensionsIndex('Delete', OnDeleteInterface::class);
    }

    protected function _createExtensionsIndex($operation, $interface)
    {
        $index = "_extensions$operation"; 
        $this->$index = [];
        foreach ($this->_extension as $extension) {
            if ($extension instanceof $interface) {
                $this->$index[] = $extension;
            }
        }
        return $this->$index;
    }

    protected function __init()
    {
        foreach ($this->_extensions as $extension) {
            if ($extension instanceof OnExtendInterface) {
                $extension->onExtend($this);
            }
        }
    }

}
