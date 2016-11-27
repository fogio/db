<?php

namespace Fogio\Db\Table;

use Fogio\Util\MiddlewareProcess as Process;

interface OnDeleteInterface
{
    public function onDelete(Process $process);
}
