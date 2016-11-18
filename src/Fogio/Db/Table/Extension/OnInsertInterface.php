<?php

namespace Fogio\Db\Table\Extension;

interface OnInsertInterface
{
    public function onInsertPre(array &$row, array &$event);

    public function onInsertPost(array &$row, array &$event);
}
