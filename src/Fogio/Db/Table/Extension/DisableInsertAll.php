<?php

namespace Fogio\Db\Table\Extension;

use Fogio\Db\Table\OnInsertAllInterface;

class DisableInsertaAll implements OnInsertAllInterface
{
    public function onInsertAll(Process $process)
    {
        throw new LogicException('Method `insertAll` is disabled. Use `insert` with foreach');
    }
}
