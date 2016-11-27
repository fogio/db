<?php

namespace Fogio\Db\Table\Extension;

use Fogio\Db\Table\OnInsertAllInterface;
use Fogio\Util\MiddlewareProcess as Process;

class DisableInsertaAll implements OnInsertAllInterface
{
    public function onInsertAll(Process $process)
    {
        throw new LogicException('Method `insertAll` is disabled. Use `insert` with foreach');
    }
}
