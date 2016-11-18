<?php

namespace Fogio\Db\Table\Extension;

interface OnUpdateInterface
{
    public function onUpdatePre(array &$data, array &$fdq, array &$event);

    public function onUpdatePost(array &$data, array &$fdq, array &$event);
}
