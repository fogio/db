<?php

namespace Fogio\Db\Table;

interface OnInsertAllInterface
{
    public function onInsertAllPre(EventInsertAll $event);

    public function onInsertAllPost(EventInsertAll $event);
}
