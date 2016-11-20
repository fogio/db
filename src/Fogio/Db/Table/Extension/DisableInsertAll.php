<?php

namespace Fogio\Db\Table\Extension;

class DisableInsertaAll implements OnInsertAllInterface
{
    public function onInsertAllPre(EventInsertAll $event)
    {
        throw new LogicException('Method `insertAll` is disabled. Use `insert` with foreach');
    }

    public function onInsertAllPost(EventInsertAll $event)
    {
    }
}
