<?php

namespace \Fogio\Db;

use Fogio\Container\ContainerTrait;
use Fogio\Middleware\MiddlewareTrait;

class Db
{
    use ContainerTrait;
    use MiddlewareTrait { setActivities as protected; process as protected; }

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

    public function setExtensions(array $extensions)
    {
        return $this->setActivities(array_merge($extensions, [$this]));
    }

    public function getExtensions()
    {
        return $this->getActivities();
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

    /* read */
    
    public function fetch($fdq)
    {
        return $this->process('onFetch', ['db' => $this, 'query' => $fdq])->result;
    }

    public function fetchAll($fdq)
    {
        return $this->process('onFetchAll', ['db' => $this, 'query' => $fdq])->result;
    }

    public function fetchCol($fdq, $colName = 0)
    {
        $col = [];
        $int = is_int($colName);
        foreach ($this->fetchall($fdq) as $row) {
            $col[] = $int ? array_values($row)[$colName] : $row[$colName];
        }
        return $col;
    }

    public function fetchVal($fdq, $colName = 0)
    {
        $row = $this->fetch($fdq);

        if (!$row) {
            return null;
        }

        return  is_int($colName) ? array_values($row)[$colName] : $row[$colName];
    }

    public function fetchKeyPair($fdq, $keyColName = 0, $valColName = 1)
    {
        $return = [];
        $intKey = is_int($keyColName);
        $intVal = is_int($valColName);
        $int    = $intKey || $intVal;

        foreach ($this->fetchAll($fdq) as $row) {
            if ($int) {
                $intRow = array_values($row);
            }
            $return[$intKey ? $intRow[$keyColName] : $row[$keyColName]] = $intVal ? $intRow[$valColName] : $row[$valColName];
        }
        return $return;
    }

    public function fetchKeyed($fdq, $colName = 0)
    {
        $return = [];
        $int = is_int($colName);

        foreach ($this->fetchAll($fdq) as $row) {
            $return[$int ? array_values($row)[$colName] : $row[$colName]] = $row;
        }

        return $return;
    }

    public function count($fdq, $expr = '*')
    {
        return $this->fetchVal([':select' => "|count($expr)"] + $fdq);
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
        return $this->process('onInsert', ['db' => $this, 'table' => $table, 'row' => $row])->result;
    }

    public function insertAll($table, array $rows)
    {
        return $this->process('onInsertAll', ['db' => $this, 'table' => $table, 'rows' => $rows])->result;
    }

    public function update($table, array $data, array $fdq)
    {
        return $this->process('onUpdate', ['db' => $this, 'table' => $table, 'data' => $data, 'query' => $fdq])->result;
    }

    public function delete($table, array $fdq)
    {
        return $this->process('onDelete', ['db' => $this, 'table' => $table, 'query' => $fdq])->result;
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
        return $this->process('onSql', ['db' => $this, 'query' => $fdq])->result;
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

    /* extension */

    public function onFetch(Process $process)
    {
        $process->result = $process->db->query($process->query)->fetch(PDO::FETCH_ASSOC);
        $process();
    }

    public function onFetchAll(Process $process)
    {
        $calc = isset($process->query[':paging']) && $process->query[':paging']->getCalcFound() === true;

        if ($calc) {
            $process->query[':prefix'] = (isset($process->query[':prefix']) ? $process->query[':prefix'] . ' ' : '') . 'SQL_CALC_FOUND_ROWS';
        }

        $process->result = $process->db->query($process->query)->fetchAll(PDO::FETCH_ASSOC);

        if ($calc) {
            $process->query[':paging']->setAll($this->fetchVal('SELECT FOUND_ROWS()'));
        }

        $process();
    }

    public function onInsert(Process $process)
    {
        $process->query[':insert'] = $process->table;
        $process->query[':set'] = $process->rows;
        $process->result = $process->db->query($process->query);
        $process();
    }

    public function onInsertAll(Process $process)
    {
        $process->query[':insert'] = $process->table;
        $process->query[':insert_rows'] = $process->rows;
        $process->result = $process->db->query($process->query);
        $process();
    }

    public function onUpdate(Process $process)
    {
        $process->query[':update'] = $process->table;
        $process->query[':set'] = $process->data;
        $process->result = $process->db->query($process->query);
        $process();

    }

    public function onDelete(Process $process)
    {
        $process->query[':delete'] = true;
        $process->query[':from'] = $process->table;
        $process->result = $process->db->query($process->query);
        $process();
    }

    public function onSql(Process $process)
    {
        $fdq = $process->query;

        // select, prefix
        $select = null;
        if (array_key_exists(':select', $fdq)) {
            if (!is_array($fdq[':select'])) {
                $select = $fdq[':select'];
            } else {
                $subjects = [];
                foreach ($fdq[':select'] as $k => $v) {
                    $subject = $v[0] === '|' ? substr($v, 1) : "`$v`";
                    if (!is_int($k)) {
                        $subject .= ' as '.($k[0] === '|' ? substr($k, 1) : "'{$process->db->escape($k)}'");
                    }
                    $subjects[] = $subject;
                }
                $select = implode(', ', $subjects);
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
                $from .= "`$table` as '{$process->db->escape($alias)}'";
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
        $having = array_key_exists(':having', $fdq) ? ' HAVING '.$process->db->sqlCondition($fdq[':having']) : null;

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

        // insert
        $insert = null;
        if (array_key_exists(':insert', $fdq)) {
            $insert = 'INSERT INTO ' . ($fdq[':insert'][0] === '|' ? substr($fdq[':insert'], 1) : "`{$fdq[':insert']}`");
        }

        // update
        $update = null;
        if (array_key_exists(':update', $fdq)) {
            $update = 'UPDATE ' . ($fdq[':update'][0] === '|' ? substr($fdq[':update'], 1) : "`{$fdq[':update']}`");
        }

        // delete
        $delete = null;
        if (array_key_exists(':delete', $fdq)) {
            if (is_string($fdq[':delete']) && $from === null) { // no from, only delete => DELETE FROM `table`
                $delete = 'DELETE';
                $from = " FROM `{$fdq[':delete']}`";
            }
            elseif ($fdq[':delete'] === true) {
                $delete = 'DELETE';
            }
            elseif (is_string($fdq[':delete'])) {
                $delete = 'DELETE ' . $fdq[':delete'];
            }
            elseif (is_array($fdq[':delete'])) {
                $subjects = [];
                foreach ($fdq[':delete'] as $k => $v) {
                    $subject = $v[0] === '|' ? substr($v, 1) : "`$v`";
                    if (!is_int($k)) {
                        $subject .= ' as '.($k[0] === '|' ? substr($k, 1) : "'{$process->db->escape($k)}'");
                    }
                    $subjects[] = $subject;
                }
                $delete = 'DELETE '.implode(', ', $subjects);
            }
        }

        // set
        $set = null;
        if (array_key_exists(':set', $fdq)) {
            $set = array();
            foreach ($fdq[':set'] as $k => $v) {
                if (is_int($k)) {
                    $set[] = $v;
                } else {
                    $set[$k] = "`$k` = " . ($v[0] === '|' ? substr($v, 1) : "'{$process->db->escape($v)}'");
                }
            }
            $set = " SET ".implode(', ', $set);
        }

        // insert_rows
        $insert_rows = null;
        if (array_key_exists(':insert_rows', $fdq)) {
            $insert_fields = array_keys($fdq[':insert_rows'][0]);
            $insert_values = [];
            foreach ($fdq[':insert_rows'] as $row) {
                $row = [];
                foreach ($insert_fields as $field) {
                    $row[$field] = $row[$field][0] === '|' ? substr($row[$field], 1) : "'{$process->db->escape($row[$field])}'";
                }
                $insert_values[] = '('.implode(', ', $row).')';
            }
            $insert_rows = " (`".implode('`, `', $insert_fields).'`) VALUES '.implode(', ', $insert_values);
        }


        unset(
            $fdq[':insert'], $fdq[':update'], $fdq[':delete'], $fdq[':select'],
            $fdq[':insert_rows'], $fdq[':set'],
            $fdq[':prefix'],
            $fdq[':from'], $fdq[':join'],
            $fdq[':group'], $fdq[':having'], $fdq[':order'],
            $fdq[':paging'], $fdq[':limit'], $fdq[':offset']
        );

        // everything else is where
        $where = $process->db->sqlCondition($fdq);
        if ($where) {
            $where = ' WHERE '.$where;
        } else {
            $where = null;
        }

        $process->result = $insert.$update.$delete.$select.$insert_rows.$set.$from.$join.$where.$group.$having.$order.$limit.$offset;
        $process();
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

    protected function __init()
    {
        foreach ($this->getActivitiesWithMethod('onExtend') as $extension) {
            $extension->onExtend($this);
        }
    }

    protected function __factory($service, $name)
    {
        if ($service !== null) {
            return $service;
        }

        return $this->$name = (new Table())->setName($name);
    }
    
}
