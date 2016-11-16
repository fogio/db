<?php

namespace Fogio\Db\Table\Extension;

class DefaultOrder implements OnFetchAllInterface, TableAwareInterface
{
    use TableAwareTrait;

    protected $order;

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function onFetchAllPre(array &$fdq, array &$event)
    {
        if (isset($fdq[':order'])) {
            return;
        }

        if (!$this->order) {
            $this->order = "`{$this->table->getKey()}` ASC";
        }

        $fdq[':order'] = $this->order;
    }

    public function onFetchAllPost(array &$fdq, array &$event, &$result)
    {
    }
}
