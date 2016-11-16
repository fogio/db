<?php

namespace Fogio\Db\Table\Extension;

use Fogio\Db\Table\Table;
use Fogio\Db\Db;

class Archive implements OnFetchAllInterface, OnExtendInterface, TableAwareInterface
{
    use TableAwareTrait;

    protected $archivedAtField;
    protected $archiveOnDelete = true;
    protected $archiveTableName;
    protected $archiveDb;

    public function setArchivedAtField($field)
    {
        $this->archivedAtField = $field;

        return $this;
    }

    public function setArchiveTableName($name)
    {
        $this->archiveTableName = $name;

        return $this;
    }

    public function getArchiveTableName()
    {
        if ($this->archiveTableName === null) {
            $this->archiveTableName = "{$this->table->getName()}_archive";
        }

        return $this->archiveTableName;
    }
    
    public function setArchiveDb(Db $db)
    {
        $this->archiveDb = $db;

        return $this;
    }


    public function getArchiveDb()
    {
        if ($this->archiveDb === null) {
            $this->archiveDb = $this->table->getDb();
        }

        return $this->archiveDb;
    }
    
    /* extension */

    public function onExtend(Table $table)
    {
        $table(['archive' => $this]);
    }

    public function onDeletePre(array &$fdq, array &$event)
    {
        if ($this->archiveOnDelete === false) {
            return;
        }

        // flag ':archive' => false
        if (array_key_exists(':archive', $fdq)) {
            $dontArchive = $fdq[':archive'] === false;
            unset($fdq[':archive']);
            if ($dontArchive) {
                return;
            }
        }

        $event['archive']['records'] = $this->table->fetchAll($fdq);
    }

    public function onDeletePost(array &$fdq, array &$event, &$result)
    {
        if (!isset($event['archive']['records'])) {
            return;
        }

        $this->storeAll($event['archive']['records']);
    }

    /* api */

    public function insert($row)
    {
        // @todo store only last version

        // fill archivedAtField
        if ($this->archivedAtField !== null && !array_key_exists($this->archivedAtField, $row)) {
            $row[$this->archivedAtField] = time();
        }

        // insert
        $this->getArchiveDb()->insert($this->getArchiveTableName(), $row);

        return $this;
    }

    public function insertAll($rows)
    {
        // @todo store only last version

        // fill archivedAtField
        if ($this->archivedAtField !== null) {
            $archivedAt = time();
            foreach ($rows as $k => $row) {
                if (!array_key_exists($this->archivedAtField, $row)) {
                    $rows[$k][$this->archivedAtField] = $archivedAt;
                }
            } 
        }

        // insert
        $this->getArchiveDb()->insertAll($this->getArchiveTableName(), $rows);

        return $this;
    }

    public function fetch(array $fdq)
    {
         return $this->getArchiveDb()->fetch($this->getArchiveTableName(), $fdq);
    }

    public function fetchAll($fdq)
    {
         return $this->getArchiveDb()->fetchAll($this->getArchiveTableName(), $fdq);
    }

    public function recover(array $fdq)
    {
         // unset $this->archivedAtField
    }

    public function recoverAll(array $fdq)
    {
         // unset $this->archivedAtField
    }

    public function delete(array $fdq)
    {
         return $this->getArchiveDb()->delete($this->getArchiveTableName(), $fdq);
    }

}
