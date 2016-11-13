<?php

namespace \Fogio\Db;

abstract class TableAbstract
{
    /**
     *
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
    
    public function getRelations()
    {
        return [];
    }

    /* read */
    
    public function fetcher()
    {
        return [':select' => $this->getFields(), ':from' => $this->getName()];
    }

    public function fetch($fdq)
    {
        return $this->_db->fetch($fdq + $this->fetcher());
    }

    public function fetchAll($fdq)
    {
        return $this->_db->fetchAll($fdq + $this->fetcher());
    }

    public function fetchCol($fdq)
    {
        return $this->_db->fetchCol($fdq + $this->fetcher());
    }

    public function fetchVal($fdq)
    {
        return $this->_db->fetchVal($fdq + $this->fetcher());
    }

    public function fetchKeyPair($fdq)
    {
        return $this->_db->fetchKeyPair($fdq + $this->fetcher());
    }

    public function fetchKeyed($fdq)
    {
        return $this->_db->fetchKeyed($fdq + $this->fetcher());
    }

    public function fetchCount($params = null, $expr = '*')
    {
        return $this->_db->fetchCount($fdq + [':from' => $this->getName()], $expr);
    }

    /* write */

    public function insert(array $row = null)
    {
        return $this->_db->insert($this->_name, $row);
    }

    public function insertAll(array $rows)
    {
        return $this->_db->insertAll($this->_name, $rows);
    }

    public function update($data, $fdq)
    {
        return $this->_db->update($this->_name, $data, $fdq);
    }

    public function delete($fdq)
    {
        return $this->_db->delete($this->_name, $fdq);
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

}
