<?php

class Schema
{

        protected function __schema()
    {
        $db = $this;
        return $this->_schema = (new Container())->__invoke([
            '__tables' => function ($schema) use ($db) {
                return $schema->_tables = $db->fetchCol('SHOW TABLES', 'TABLE_NAME');
            },
            '__factory' => function ($service, $name,  $schema) use ($db) {
                if (!in_array($name, $this->_tables)) {
                    throw new LogicException("Table `$name` doesn't exist");
                }
                $schema->$name = (object)[
                    'raw' => $db->fetchAll("SHOW COLUMNS FROM `" . $db->escape($name) ."`"),
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