<?php

namespace \Fogio\Db;

interface LinkerInterface
{
    /**
     * Add join statment.
     *
     * @param Model        $model
     * @param array|string $to
     * @param array|string $from
     * @param string       $joinType
     * @param bool|array   $fields
     */
    public function addLink(Model $model, $to, $from = null, $join = 'JOIN', $fields = true);
}
