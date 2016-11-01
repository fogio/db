<?php

namespace Fogio\Db;

abstract class LinkerAbstract 
{
    
    protected $fromAlias;
    protected $fromTable;
    protected $fromModel;
    protected $toAlias;
    protected $toTable;
    protected $toModel;

    public function addLink(Model $model, $to, $from = null, $joinType = 'JOIN', $fields = true)
    {
        
        // resolve from - table, alias, model
        if ($from === null) {
            $this->fromTable = $model->getTable();
            $this->fromAlias = null;
        } elseif (is_string($from)) {
            $this->fromTable = $from;
            $this->fromAlias = null;
        } elseif (is_array($from)) {
            $this->fromAlias = key($from);
            $this->fromTable = current($from);
        }
        $this->fromModel = $model->getDb()->{$this->fromTable};
        
        
        // to
        $toAlias = null;
        $toSuffix = null;
        if (is_array($to)) {
            $toAlias = key($to);
            $to = current($to);
        }
        if (strpos($to, ':') !== false) {
            list($to, $toSuffix) = explode(':', $to);
            $toSuffix = "_$toSuffix";
        }
        $toModel = $model->getDb()->{$to};


        // fields
        if ($fields === true) {
            $fields = $model->getDb()->{$to}->getFields();
        }
        if (is_string($fields)) {
            $model->addField($fields);
        } elseif (is_array($fields)) {
            $prefix = $toAlias ?: $to;
            foreach ($fields as $k => $v) {
                $fields[$k] = "|`$prefix`.`$v`";
            }
        }

        // n:1? 1:n? 1:1?
        $ref = "{$from}_id_{$to}{$toSuffix}";
        $dep = "{$to}_id_{$from}{$toSuffix}";
        $toLink = $fromLink = null;
        if ($fromModel->hasField($ref)) { // relation n:1 (ref)
            $toLink = $toModel->getKey();
            $fromLink = $ref;
        } elseif ($toModel->hasField($dep)) { // relation 1:n (dep)
            $toLink = $dep;
            $fromLink = $fromModel->getKey();
        } else { // 1:1
            $toLink = $toModel->getKey();
            $fromLink = $fromModel->getKey();
        }

        $model->addJoin(
            $joinType
            ."`$to`".($toAlias !== null ? " as '{$model->escape($toAlias)}'" : '')
            .' ON ('
            .($toAlias !== null ? "`{$model->escape($toAlias)}`." : '')."`$toLink`"
            .' = '
            .($fromAlias !== null ? "`{$model->escape($fromAlias)}`." : '')."`$fromLink`"
            .')'
        );
    }    
}
