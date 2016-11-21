<?php

namespace Fogio\Db\Table;

interface OnFetchAllInterface
{
    public function onFetchAll(Process $process);
}
