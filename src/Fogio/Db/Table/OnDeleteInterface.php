<?php

namespace Fogio\Db\Table;

interface OnDeleteInterface
{
    public function onDeletePre(EventDelete $event);

    public function onDeletePost(EventDelete $event);
}
