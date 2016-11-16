<?php

namespace Fogio\Db\Table\Extension;

class DisableInsertaAll implements OnInsertAllInterface
{
    public function onInsertAllPre(array &$rows, array &$event)
    {
        throw new LogicException('Method `insertAll` is disabled. Use `insert` with foreach');
    }

    public function onInsertAllPost(array &$rows, array &$event, &$result)
    {
    }
}
