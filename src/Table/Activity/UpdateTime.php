<?php

namespace Fogio\Db\Table\Activity;

use Fogio\Middleware\Process;

class UpdateTime
{
    protected $field;

    public function setField($field)
    {
        $this->field = $field;
    }

    public function onUpdate(Process $process)
    {
        if ($this->field === null) {
            $this->field = "{$process->table->getName()}_update";
        }

        if (!array_key_exists($this->field, $process->data)) {
            $process->data[$this->field] = time();
        }

        $process();
    }

}
