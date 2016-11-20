<?php

namespace Fogio\Db\Table;

interface OnUpdateInterface
{
    public function onUpdatePre(EventUpdate $event);

    public function onUpdatePost(EventUpdate $event);
}
