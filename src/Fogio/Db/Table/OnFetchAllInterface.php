<?php

namespace Fogio\Db\Table;

interface OnFetchAllInterface
{
    public function onFetchAllPre(EventFetchAll $event);

    public function onFetchAllPost(EventFetchAll $event);
}
