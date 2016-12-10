<?php

namespace Fogio\Db\Table\Extension;

use Fogio\Db\Table\OnFetchAllInterface;
use Fogio\Util\MiddlewareProcess as Process;

class DefaultOrder implements OnFetchAllInterface
{
    protected $order;

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function onFetchAll(Process $process)
    {
        if (!isset($process->query[':order'])) {
            if ($this->order === null) {
                $this->order = "`{$process->table->getKey()}` ASC";
            }
            $process->query[':order'] = $this->order;
        }

        $process();
    }

}
