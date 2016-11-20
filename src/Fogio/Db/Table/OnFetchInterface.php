<?php

namespace Fogio\Db\Table;

interface OnFetchInterface
{
    public function onFetchPre(EventFetch $event);

    public function onFetchPost(EventFetch $event);
}
