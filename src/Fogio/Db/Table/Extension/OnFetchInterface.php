<?php

namespace Fogio\Db\Table\Extension;

interface OnFetchInterface
{
    public function onFetchPre(array &$fdq, array &$event);

    public function onFetchPost(array &$fdq, array &$event);
}
