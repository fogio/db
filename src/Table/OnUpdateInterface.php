<?php

namespace Fogio\Db\Table;

use Fogio\Util\MiddlewareProcess as Process;

interface OnUpdateInterface
{
    public function onUpdate(Process $process);
}
