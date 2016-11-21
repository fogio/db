<?php

namespace Fogio\Db\Table;

interface OnDeleteInterface
{
    public function onDelete(Process $process);
}
