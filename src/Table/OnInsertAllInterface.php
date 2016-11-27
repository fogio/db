<?php

namespace Fogio\Db\Table;

use Fogio\Util\MiddlewareProcess as Process;

interface OnInsertAllInterface
{
    public function onInsertAll(Process $process);
}
