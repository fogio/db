<?php

namespace Fogio\Db\Table\Extension;

interface OnDeleteInterface
{
    public function onDeletePre(array &$fdq, array &$event);

    public function onDeletePost(array &$fdq, array &$event);
}
