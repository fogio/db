<?php

namespace Fogio\Db\Table;

interface OnInsertInterface
{
    public function onInsert(Process $process);
}
