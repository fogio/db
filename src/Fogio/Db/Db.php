<?php

namespace \Fogio\Db;

use Fogio\Container\Container;

class Db extends Container
{
    
    /* config */
    
    public function setPdo($pdo)
    {
        $this->_pdo = $pdo;
        
        return $this;
    }
    
    public function setPdoProvider(Callable $pdo)
    {
        $this(['_pdo' => $pdo]);
        
        return $this;
    }
    
    public function getPdo()
    {
        return $this->_pdo;
    }
    
    public function setPagingProvider(Callable $factory)
    {
        $this(['_paging' => $factory]);
        
        return $this;
    }
    
    public function getPaging()
    {
        return $this->_paging;
    }
    
    /* query base */

    /**
     * @return PDOStatement
     */
    public function query($fdq)
    {
        return $this->_pdo->query($this->sql($fdq));
    }

    /**
     * @return PDOStatement
     */
    public function prepare($fdq)
    {
        return $this->_pdo->prepare($this->sql($fdq));
    }
    
    public function beginTransaction()
    {
        return $this->_pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->_pdo->commit();
    }

    public function rollBack()
    {
        return $this->_pdo->rollBack();
    }

    /* fetch */
    
    public function count($fdq, $expr = '*')
    {
        return $this->fetchVal([':select' => "|count($expr)"] + $fdq);
    }
    
    public function fetch($fdq)
    {
        return $this->query($fdq)->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchAll($fdq)
    {
        $calc = isset($fdq[':paging']) && $fdq[':paging']->getCalcFound() === true;
        
        if ($calc) {
            $fdq[':prefix'] = (isset($fdq[':prefix']) ? $fdq[':prefix'] . ' ' : '') . 'SQL_CALC_FOUND_ROWS';
        }
        
        $data = $this->query($fdq)->fetchAll(PDO::FETCH_ASSOC);
        
        if ($calc) {
            $fdq[':paging']->setAll($this->fetchVal('SELECT FOUND_ROWS()'));
        }
                
        return $data;
    }

    public function fetchCol($fdq, $colName = 0)
    {
        $col = [];
        foreach ($this->query($fdq)->fetchall() as $row) {
            $col[] = $row[$colName];
        }
        return $col;
    }

    public function fetchVal($fdq, $colName = 0)
    {
        $row = $this->query($fdq)->fetch();

        return array_key_exists($colName, $row) ? $row[$colName] : null;
    }

    public function fetchKeyPair($fdq)
    {
        return $this->query($fdq)->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    public function fetchKeyed($fdq)
    {
        return $this->query($fdq)->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
    }

    /* write */
    
    /**
     * @param string $table
     * @param array  $row
     *
     * @return self
     */
    public function insert($table, array $row)
    {
        $set = [];

        foreach ($row as $k => $v) {
            if (is_int($k)) {
                $set[] = $v;
            } else {
                $set[$k] = "`$k` = '{$this->escape($v)}'";
            }
        }

        return $this->query("INSERT INTO `{$table}` SET ".implode(', ', $set));
    }

    public function insertAll($table, array $rows)
    {
        $fields = array_keys($rows[0]);
        $values = [];

        foreach ($rows as $row) {
            $value = [];
            foreach ($fields as $field) {
                $value[$field] = "'{$this->escape($row[$field])}'";
            }
            $values[] = '('.implode(', ', $value).')';
        }

        return $this->query("INSERT INTO `{$table}` (`".implode('`, `', $fields).'`) VALUES '.implode(', ', $values));
    }

    public function update($table, array $data, array $fdq)
    {
        $set = array();

        foreach ($data as $k => $v) {
            if (is_int($k)) {
                $set[] = $v;
            } else {
                $set[$k] = "`$k` = '{$this->escape($v)}'";
            }
        }

        return $this->query("UPDATE `{$table}` SET ".implode(', ', $set).' WHERE '.$this->sqlCondition($fdq));
    }

    public function delete($table, array $fdq)
    {
        return $this->query([':prefix' => 'DELETE', ':from' => $table] + $fdq);
    }
    
    /* helpers */
    
    public function escape($string)
    {
        return $this->_pdo->quote($string);
    }

    public function lastInsertId()
    {
        return $this->_pdo->lastInsertId();
    }

    /* FDQ- Fogio Db Query */
    
    /**
     * Builds fragmenet or full raw sql query.
     *
     * $fdq options:
     * - ':select' => ['post_id', 'title' => 'post_title']  // `post_id`, `post_title` as 'title'
     * - ':select' => 'post_id, post_title as title' // post_id, post_title as title
     * - ':select' => ['|count' => '|COUNT(*)']  // COUNT(*) as count
     * - ':prefix' => 'SQL_NO_CACHE DISTINCT'
     * - ':from'   => 'post'
     * - ':from'   => ['p' => 'post']
     * - ':join'   => ['JOIN author ON (author_id = post_id_author)', 'LEFT JOIN img ON (author_id_img = img_id)']
     * - ':group'  => 'post_id'
     * - ':having' => 'post_id > 0'
     * - ':having' => ['post_id >' =>  '0']
     * - ':order'  => 'post_published DESC'
     * - ':paging' => \Fogio\Paging\PagingInterface
     * - ':limit'  => 10,
     * - ':offset' => 0,
     * - 'post_level' => [1, 2, 3] // `post_level` IN ('1', '2',  '3')
     * - 'post_level BETWEEN' => [4, 5] // `post_level` BETWEEN '4' AND '5'
     * - 'post_level <>' => 4 // `post_level` <> '4'
     * - '|post_level <>' => 4 // post_level <> '4'
     * - "|post_level != '{a}')" => ['{a}' => 4] // post_level != '4'
     * - ':operator' => 'AND' // values: AND, OR; default: AND; logical operator that joins all conditions
     * - [':operator' => 'OR', 'post_level' => '1'
     *    [':operator' => 'OR', 'post_level' => '2', 'post_level' => '3']]
     *   // post_level = '1' OR (post_level = '2' OR  post_level = '3')
     *
     * @param array $fdq
     *
     * @return string Sql
     */
    public function sql($fdq)
    {
        if (is_string($fdq)) {
            return $fdq;
        }
        
        // select, prefix
        $select = null;
        if (isset($fdq[':select'])) {
            if (!is_array($fdq[':select'])) {
                $select = $fdq[':select'];
            } else {
                $cols = [];
                foreach ($fdq[':select'] as $k => $v) {
                    $col = $v[0] === '|' ? substr($v, 1) : "`$v`";
                    if (is_int($k)) {
                        $col .= ' '.($k[0] === '|' ? substr($k, 1) : "'{$this->escape($k)}'");
                    }
                    $cols[] = $col;
                }
                $select = implode(', ', $cols);
            }
            $select = 'SELECT '
                    .(array_key_exists(':prefix', $fdq) ? ' '.$fdq[':prefix'] : '')
                    .$select;
        }

        // from, alias
        $from = null;
        if (array_key_exists(':from', $fdq)) {
            $from = ' FROM ';
            if (is_array()) {
                $table = reset($fdq[':from']);
                $alias = key($fdq[':from']);
                $from .= "`$table` as '{$this->escape($alias)}'";
            } elseif ($fdq[':from'][0] === '*') {
                $from .= substr($fdq[':from'], 1);
            } else {
                $from .= "`{$fdq[':from']}`";
            }
        }

        // join
        $join = array_key_exists(':join', $fdq) ? ' '.implode(' ', $fdq[':join']) : null;

        // group
        $group = array_key_exists(':group', $fdq) ? ' GROUP BY '.$fdq[':group'] : null;

        // having
        $having = array_key_exists(':having', $fdq) ? ' HAVING '.$this->sqlCondition($fdq[':having']) : null;

        // group
        $order = array_key_exists(':order', $fdq) ? ' ORDER BY '.$fdq[':order'] : null;

        // limit, offset
        $limit = null;
        $offset = null;
        if (array_key_exists(':paging', $fdq)) {
            $limit = ' LIMIT '.$fdq[':paging']->getLimit();
            $offset = ' OFFSET '.$fdq[':paging']->getOffset();
        } else {
            if (array_key_exists(':limit', $fdq)) {
                $limit = ' LIMIT '.$fdq[':limit'];
            }
            if (array_key_exists(':offset', $fdq)) {
                $offset = ' OFFSET '.$fdq[':offset'];
            }
        }

        unset($fdq[':select'], $fdq[':prefix'],
              $fdq[':from'], $fdq[':join'],
              $fdq[':group'], $fdq[':having'], $fdq[':order'],
              $fdq[':paging'], $fdq[':limit'], $fdq[':offset']);

        // everything else is where
        $where = $this->sqlCondition($fdq);
        if ($where) {
            $where = ' WHERE '.$where;
        } else {
            $where = null;
        }

        return $select.$from.$join.$where.$group.$having.$order.$limit.$offset;
    }

    public function sqlCondition($condition)
    {
        if (is_string($condition)) {
            return $condition;
        }

        $logical = ' AND ';
        $sql = [];

        foreach ($condition as $key => $value) {

            // <int> => [], <int> => '<raw sql>'
            if (is_int($key)) {
                $sql[] = is_array($value) ? '('.$this->sqlCondition($value).')' : $value;
                continue;
            }

            // '#(a = {a} OR b = {b})' => [{a} => '1', {b} => '2']
            if ($key[0] === '|' && is_array($value)) {
                $key = substr($key, 1);
                foreach ($value as $find => $replace) {
                    $key = str_replace($find, $this->escape($replace), $key);
                }
                $sql[] = $key;
            }

            // logical operator - AND, OR
            if ($key === ':operator') {
                $logical = ' '.trim($value).' ';
                continue;
            }

            $comparison = '=';

            if (strpos($key, ' ') !== false) {
                list($key, $comparison) = explode(' ', $key, 2);
            }

            $key = $key[0] === '|' ? substr($key, 1) : "`$key`";

            if (is_array($value) && $comparison === '=') {
                $comparison = 'IN';
            }

            switch ($comparison) {

                case 'BETWEEN':
                case 'NOT BETWEEN':
                    $sql[] = "$key $comparison"
                             ." '{$this->escape($value[0])}'"
                             ." AND '{$this->escape($value[1])}'";
                    break;

                case 'IN':
                case 'NOT IN':
                    foreach ($value as $k => $v) {
                        $value[$k] = $this->escape($v);
                    }
                    $sql[] = "$key $comparison ('".implode("','", $value)."')";
                    break;

                default:
                    $sql[] = "$key $comparison '{$this->escape($value)}'";
                    break;

            }
        }

        return implode($logical, $sql);
    }

    /* private api */

    protected function __pdo()
    {
        throw new LogicException('Service `_pdo` not defined');
    }
    
    protected function __paging()
    {
        return new Paging();
    }

    protected function __factory($service, $name)
    {
        if ($service !== null) {
            return $service;
        }

        return $this->$name = (new Table())->setName($name);
    }

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
