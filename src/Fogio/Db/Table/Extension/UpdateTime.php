<?php

namespace Fogio\Db\Table\Extension;

use Fogio\Db\Table\Table;

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

    public function onUpdatePre(array &$data, array &$fdq, array &$event)
    {
        if (array_key_exists($this->field, $data)) {
            return;
        }
        $data[$this->field] = time();
    }

    public function onUpdatePost(array &$data, array &$fdq, array &$event, &$result)
    {
    }

}
