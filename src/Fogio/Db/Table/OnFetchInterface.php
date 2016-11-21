<?php

namespace Fogio\Db\Table;

interface OnFetchInterface
{
    public function onFetch(Process $process);
}
