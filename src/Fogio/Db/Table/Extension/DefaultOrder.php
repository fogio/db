<?php

namespace Fogio\Db\Table\Extension;

use Fogio\Db\Table\OnFetchAllInterface;
use Fogio\Db\Table\Process;
use Fogio\Db\Table\TableAwareInterface;
use Fogio\Db\Table\TableAwareTrait;

class DefaultOrder implements OnFetchAllInterface, TableAwareInterface
{
    use TableAwareTrait;

    protected $order;

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function onFetchAll(Process $process)
    {
        if (isset($process->fdq[':order'])) {
            return;
        }

        if (!$this->order) {
            $this->order = "`{$this->table->getKey()}` ASC";
        }

        $process->fdq[':order'] = $this->order;

        $process();
    }

}
