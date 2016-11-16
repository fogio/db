<?php

namespace Fogio\Db\Table\Extension;

interface OnFetchAllInterface
{
    public function onFetchAllPre(array &$fdq, array &$event);

    public function onFetchAllPost(array &$fdq, array &$event, &$result);
}
