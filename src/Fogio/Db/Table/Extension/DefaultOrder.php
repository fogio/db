<?php

namespace Fogio\Db\Table\Extension;

use Fogio\Db\Table\OnFetchAllInterface;
use Fogio\Db\Table\EventFetchAll;
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

    public function onFetchAllPre(EventFetchAll $event)
    {
        if (isset($event->fdq[':order'])) {
            return;
        }

        if (!$this->order) {
            $this->order = "`{$this->table->getKey()}` ASC";
        }

        $event->fdq[':order'] = $this->order;
    }

    public function onFetchAllPost(EventFetchAll $event)
    {
    }
}
