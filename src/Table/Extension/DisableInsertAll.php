<?php

namespace Fogio\Db\Table\Extension;

use Fogio\Middleware\Process;

class DisableInsertaAll
{
    public function onInsertAll(Process $process)
    {
        throw new LogicException('Method `insertAll` is disabled. Use `insert` with foreach');
    }
}
