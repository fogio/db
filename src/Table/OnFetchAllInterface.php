<?php

namespace Fogio\Db\Table;

use Fogio\Util\MiddlewareProcess as Process;

interface OnFetchAllInterface
{
    public function onFetchAll(Process $process);
}
