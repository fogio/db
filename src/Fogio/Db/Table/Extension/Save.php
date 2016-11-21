<?php

namespace Fogio\Db\Table\Extension;

use Fogio\Db\Table\Table;
use Fogio\Db\Db;

class Archive implements OnExtendInterface, TableAwareInterface
{
    use TableAwareTrait;
    
    public function onExtend(Table $table)
    {
        $table(['save' => $this]);
    }

    public function invoke($row)
    {
        return $this->save($row);
    }

    public function save($row)
    {
        $key = $this->table->getKey();
        
        if ($key === null) {
            throw new LogicException();
        }
        
        if ($row[$key] === null) {
            return $this->table->insert($row);
        } else {
            $fdq = [$key => $row[$key]];
            unset($row[$key]);
            return $this->table->update($row, $fdq);
        }
    }

}
