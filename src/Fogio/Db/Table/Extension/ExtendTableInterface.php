<?php

namespace Fogio\Db\Table\Extension;

use Fogio\Db\Table\Table;

interface OnExtendInterface
{
    public function onExtend(Table $table);
}
