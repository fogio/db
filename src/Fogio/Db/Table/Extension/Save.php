<?php

namespace Fogio\Db\Table\Extension;

use Fogio\Db\Db;
use Fogio\Db\Table\Table;
use Fogio\Db\Table\OnExtendInterface;
use Fogio\Container\InvokableInterface;

class Save implements OnExtendInterface, InvokableInterface
{
    protected $table;

    public function onExtend(Table $table)
    {
        $this->table = $table(['save' => $this]);
        $this->key = $this->table->getKey();
        if ($this->key === null) {
            throw new LogicException();
        }
    }

    public function invoke($row)
    {
        return $this->save($row);
    }

    public function save($row)
    {
        if ($row[$this->key] === null) {
            return $this->table->insert($row);
        } else {
            $fdq = [$this->key => $row[$this->key]];
            unset($row[$this->key]);
            return $this->table->update($row, $fdq);
        }
    }

}
