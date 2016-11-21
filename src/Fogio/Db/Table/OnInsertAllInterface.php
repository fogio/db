<?php

namespace Fogio\Db\Table;

interface OnInsertAllInterface
{
    public function onInsertAll(Process $process);
}
