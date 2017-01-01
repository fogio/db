<?php

namespace \Fogio\Db\Activity;

use Fogio\Db\Db;
use Fogio\Container\Container;

class DynamicSchema
{

    public function onExtend(Db $db)
    {
        $db(['_schema' => $this]);
    }

    protected function __schema()
    {
        $db = $this;
        return $this->_schema = (new Container())->__invoke([

            '__tables' => function ($schema) use ($db) {
                return $schema->_tables = $db->getPdo()->query('SHOW TABLES', 'TABLE_NAME')->fetchColumn();
            },

            '__factory' => function ($service, $name, $schema) use ($db) {
                if (!in_array($name, $this->_tables)) {
                    throw new LogicException("Table `$name` doesn't exist");
                }
                $schema->$name = (object)[
                    'raw' => $db->getPdo->query("SHOW COLUMNS FROM `" . $db->escape($name) ."`")->fetchAll(PDO::FETCH_ASSOC),
                    'fields' => [],
                    'key' => null,
                ];
                foreach ($schema->$name->raw as $col) {
                    $schema->$name->fields[] = $col['Field'];
                    if ($col['Key'] === 'PRI') {
                        $schema->$name->key = $col['Field'];
                    }
                }

            },
                
        ]);
    }

}