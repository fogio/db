<?php

namespace Fogio\Db\Table\Extension;

use Fogio\Db\Table\OnFetchAllInterface;
use Fogio\Db\Table\Process;

class DefaultOrder implements OnFetchAllInterface
{
    protected $order;

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function onFetchAll(Process $process)
    {
        if (!isset($process->fdq[':order'])) {
            if ($this->order === null) {
                $this->order = "`{$process->table->getKey()}` ASC";
            }
            $process->fdq[':order'] = $this->order;
        }

        $process();
    }

}
