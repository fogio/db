<?php

namespace Fogio\Db\Table\Extension;

use Fogio\Container\ContainerTrait;
use Fogio\Db\Db;
use Fogio\Db\Table\Table;
use Fogio\Db\Table\OnInsertInterface;
use Fogio\Db\Table\OnInsertAllInterface;
use Fogio\Db\Table\OnUpdateInterface;
use Fogio\Db\Table\OnDeleteInterface;
use Fogio\Db\Table\Event;
use Fogio\Db\Table\EventInsert;
use Fogio\Db\Table\EventInsertAll;
use Fogio\Db\Table\EventUpdate;
use Fogio\Db\Table\EventDelete;


class Vcs implements 
    TableAwareInterface, OnExtendInterface, 
    OnInsertInterface, OnInsertAllInterface,
    OnUpdateInterface, OnDeleteInterface 
{
    use TableAwareTrait;
    use ContainerTrait;

    protected $_fieldCommitAt  = 'vcs_insert';  // integer
    protected $_fieldAction    = 'vcs_action';  // varchar, actions: `insert`, `update`, `delete`
    protected $_fieldVersionNo = 'vcs_version'; // integer
    protected $_fieldInfo      = 'vcs_info';    // text
    protected $_onInsertPost = false;
    protected $_onUpdatePre  = false;
    protected $_onUpdatePost = false;
    protected $_onDeletePre  = false;
    protected $_onDeletePost = false;

    /* setup */

    public function setDb(Db $db)
    {
        $this->_db = $db;

        return $this;
    }

    public function setTable($name)
    {
        $this->_table = $name;

        return $this;
    }
    
    public function setFieldCommitAt($fieldCommitAt)
    {
        $this->_fieldCommitAt = $fieldCommitAt;
    
        return $this;
    }
    
    public function setFieldAction($fieldAction)
    {
        $this->_fieldAction = $fieldAction;
    
        return $this;
    }
    
    public function setFieldVersionNo($fieldVersionNo)
    {
        $this->_fieldVersionNo = $fieldVersionNo;
    
        return $this;
    }
    
    public function setFieldInfo($fieldInfo)
    {
        $this->_fieldInfo = $fieldInfo;
    
        return $this;
    }

    public function setStrategy($onInsertPost, $onUpdatePre, $onUpdatePost, $onDeletePre, $onDeletePost)
    {
        $this->_onInsertPost = $onInsertPost;
        $this->_onUpdatePre  = $onUpdatePre;
        $this->_onUpdatePost = $onUpdatePost;
        $this->_onDeletePre  = $onDeletePre;
        $this->_onDeletePost = $onDeletePost;
    }

    public function setStrategyLog()
    {
        $this->setStrategy(true, false, true, false, true);
    }

    public function setStrategyDiff()
    {
        $this->setStrategy(false, true, false, true, false);
    }

    public function setStrategySoftDelete()
    {
        $this->setStrategy(false, false, false, true, false);
    }

    /* extension */

    public function onExtend(Table $table)
    {
        $table(['archive' => $this]);
    }

    public function onInsertPre(EventInsert $event)
    {
        $this->prepare($event);
    }

    public function onInsertPost(EventInsert $event)
    {
        if (!$this->_onInsertPost) {
            return;
        }

        $event->vcs->snapshot = $event->row;

        $this->commit($event);
    }

    public function onInsertAllPre(EventInsertAll $event)
    {
        throw new LogicException('Method `insertAll` is disabled. Use `insert` with foreach');
    }

    public function onInsertAllPost(EventInsertAll $event)
    {
    }
    
    public function onUpdatePre(EventUpdate $event)
    {
        // @todo
    }

    public function onUpdatePost(EventUpdate $event)
    {
        // @todo
    }

    public function onDeletePre(EventDelete $event)
    {
        // @todo
    }

    public function onDeletePost(EventDelete $event)
    {
        // @todo
    }


    /* api */

    public function fetch(array $fdq)
    {
         return $this->_db->fetch($this->_table, $fdq);
    }

    public function fetchAll($fdq)
    {
         return $this->_db->fetchAll($this->_table, $fdq);
    }

    public function recover(array $fdq)
    {
        // @todo
        // auto order version field DESC
    }

    public function delete(array $fdq)
    {
         return $this->_db->delete($this->_table, $fdq);
    }

    /* helpers */

    protected function prepare($event)
    {
        $event->vcs = new stdClass();
        if (array_key_exists(':vcs_info', $event->row)) {
            $event->vcs->info = $event->row[':vcs_info'];
            unset($event->row[':vcs_info']);
        }
        if (array_key_exists(':vcs_enabled', $event->row)) {
            $event->vcs->enabled = $event->row[':vcs_enabled'];
            unset($event->row[':vcs_enabled']);
        }
    }

    protected function commit(Event $event)
    {
        if (isset($event->vcs->enabled) && $event->vcs->enabled === false) {
            return;
        }

        // commit at
        if ($this->_fieldCommitAt !== null) {
            $event->vcs->snapshot[$this->_fieldCommitAt] = time();
        }

        // action
        if ($this->_fieldAction !== null) {
            $event->vcs->snapshot[$this->_fieldCommitAt] = $event->id;
        }

        // version
        if ($this->_fieldVersionNo !== null) {
            $event->vcs->snapshot[$this->_fieldVersionNo] = $event->id !== 'insert'
                ? 1 + $this->_db->fetchVal([
                    ':select' => "|MAX(`{$this->_db->escape($this->_fieldVersionNo)}`)",
                    ':from'   => $this->_table,
                ])
                : '1';
        }

        // info
        if ($this->_fieldInfo !== null && isset($event->vcs->info)) {
            $event->vcs->snapshot[$this->_fieldInfo] = json_encode($$event->vcs->info);
        }

        $this->_db->insert($this->_table, $event->vcs->snapshot);
    }

    /* defaults */

    protected function __db()
    {
        return $this->_db = $this->table->getDb();
    }
    
    public function __table()
    {
        return $this->_table = $this->table->getName() . "_vcs";
    }

}
