<?php

namespace Fogio\Db\Table\Extension;

use Fogio\Db\Table\OnUpdateInterface;
use Fogio\Db\Table\TableAwareInterface;

class UpdateTime implements OnUpdateInterface, TableAwareInterface
{
    use TableAwareTrait;

    protected $field;

    public function setField($field)
    {
        $this->field = $field;
    }

    public function getField()
    {
        if ($this->field === null) {
            $this->field = "{$this->table->getName()}_update";
        }

        return $this->field;
    }

    public function onUpdate(Process $process)
    {
        if (array_key_exists($this->field, $process->data)) {
            return;
        }
        $process->data[$this->field] = time();

        $process();
    }


}
