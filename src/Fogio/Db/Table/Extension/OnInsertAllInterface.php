<?php

namespace Fogio\Db\Table\Extension;

interface OnInsertAllInterface
{
    public function onInsertAllPre(array &$rows, array &$event);

    public function onInsertAllPost(array &$rows, array &$event, &$result);
}
