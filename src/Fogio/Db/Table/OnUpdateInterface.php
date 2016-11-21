<?php

namespace Fogio\Db\Table;

interface OnUpdateInterface
{
    public function onUpdate(Process $process);
}
