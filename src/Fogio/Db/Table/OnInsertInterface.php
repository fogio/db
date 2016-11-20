<?php

namespace Fogio\Db\Table;

interface OnInsertInterface
{
    public function onInsertPre(EventInsert $event);

    public function onInsertPost(EventInsert $event);
}
