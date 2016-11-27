<?php

namespace Fogio\Db\Table;

use Fogio\Util\MiddlewareProcess as Process;

interface OnFetchInterface
{
    public function onFetch(Process $process);
}
