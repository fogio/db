<?php

namespace Fogio\Db\Table;

use Fogio\Util\MiddlewareProcess as Process;

interface OnInsertInterface
{
    public function onInsert(Process $process);
}
