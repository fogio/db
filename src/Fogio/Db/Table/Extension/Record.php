<?php

namespace \Fogio\Db\Table\Extension;

use Fogio\Paging\PagingInterface;

class Record implements OnExtendInterface
{
    protected $_table;
    protected $_alias;
    protected $_key;
    protected $_field;
    protected $_prefix;
    protected $_param;
    protected $_result;

    public function onExtend(Table $table)
    {
        $table(['record' => function ($table) {
            return (new self())->setTable($table)->setFields($table->getFields())->setKey($table->getKey());
        }]);
    }

    /* setup */

    public function getTable()
    {
        return $this->_table;
    }

    public function setKey($key)
    {
        $this->_key = $key;

        return $this;
    }

    public function getKey()
    {
        return $this->_key;
    }

    public function setFields(array $fields)
    {
        $this->_field = $fields;

        return $this;
    }

    public function getFields()
    {
        return $this->_field;
    }

    public function addField($field, $alias = null)
    {
        if ($alias === null) {
            $this->_field[] = $field;
        } else {
            $this->_field[$alias] = $field;
        }

        return $this;
    }

    public function addFields(array $fields)
    {
        foreach ($fields as $alias => $field) {
            if (is_int($alias)) {
                $this->_field[] = $field;
            } else {
                $this->_field[$alias] = $field;
            }
        }

        return $this;
    }

    public function removeField($field)
    {
        foreach ($this->_field as $alias => $name) {
            if (is_int($alias)) {
                if ($name === $field) {
                    unset($this->_field[$alias]);
                }
            } 
            else {
                if ($alias === $field) {
                    unset($this->_field[$alias]);
                }
            }
        }

        return $this;
    }

    public function hasField($field)
    {
        foreach ($this->_field as $alias => $name) {
            if ($name === $field) {
                return true;
            }
        }

        return false;
    }

    /* value */

    public function setId($id)
    {
        $this->_id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setVal(array $val)
    {
        foreach ($this->_field as $field) {
            if (isset($val[$field]) && $field !== $this->_key) {
                $this->{$field} = $val[$field];
            }
        }

        return $this;
    }

    public function getVal()
    {
        $val = [];
        foreach ($this->_field as $i) {
            $val[$i] = $this->{$i};
        }
        if ($this->_key !== null) {
            $val[$this->_key] = $this->getId();
        }

        return $val;
    }

    public function setValAndFields($data)
    {
        $this->setFields(array_keys($data));
        $this->setVal($data);

        return $this;
    }

    public function setValAndId($data)
    {
        $this->setVal($data);
        if ($this->_key !== null) {
            $this->{$this->_key} = $data[$this->_key];
        }

        return $this;
    }

    public function setResult($result)
    {
        $this->_result = $result;
        foreach ($this->_field as $field) { /* @todo handle is_int? */
            $this->{$field} = null;
        }
        $this->{$this->_key} = null;
        $this->setVal($result);
        $this->{$this->_key} = $result[$this->_key];
        return $this;
    }

    public function getResult()
    {
        return $this->_result;
    }

    /* param */

    public function setParam($name, $value)
    {
        $this->_param[$name] = $value;

        return $this;
    }

    public function hasParam($name)
    {
        return array_key_exists($name, $this->_param);
    }

    public function getParam($name)
    {
        return $this->_param[$name];
    }

    public function removeParam($name)
    {
        unset($this->_param);
    }

    public function setParams(array $params)
    {
        foreach ($params as $name => $value) {
            if (is_int($name)) {
                $this->_param[] = $name;
            } else {
                $this->_param[$name] = $value;
            }
        }
    }

    public function hasParams(array $params)
    {
        foreach ($params as $name => $value) {
            if ($this->hasParam($name, $value)) {
                continue;
            }

            return false;
        }

        return true;
    }

    public function getParams()
    {
        return $this->_param;
    }

    public function removeParams()
    {
        $this->_param = array();

        return $this;
    }

    public function param($arg1 = null, $arg2 = null)
    {
        switch (func_num_args()) {

            case 0:
                return $this->getParams();

            case 1:
                if (is_array($arg1)) {
                    return $this->setParams($arg1);
                } elseif (is_null($arg1)) {
                    return $this->removeParams();
                } else {
                    return $this->getParams($arg1);
                }

            case 2:
                $this->_param[$arg1] = $arg2;

                return $this;

        }
    }

    public function setParamId($id)
    {
        $this->_param[$this->_key] = $id;

        return $this;
    }

    public function getParamId($id)
    {
        return $this->_param[$this->_key];
    }

    public function setPrefix($prefix)
    {
        $this->_param[':prefix'] = $prefix;

        return $this;
    }

    public function getPrefix()
    {
        return $this->_param[':prefix'];
    }

    public function setAlias($alias)
    {
        $this->_param[':alias'] = $alias;

        return $this;
    }

    public function getAlias()
    {
        return $this->_param[':alias'];
    }

    public function setGroup($group)
    {
        $this->_param[':group'] = $group;

        return $this;
    }

    public function getGroup()
    {
        return $this->_param[':group'];
    }

    public function setHaving($having)
    {
        $this->_param[':having'] = $having;

        return $this;
    }

    public function getHaving()
    {
        return $this->_param[':having'];
    }

    public function setOrder($order)
    {
        $this->_param[':order'] = $order;

        return $this;
    }

    public function getOrder()
    {
        return $this->_param[':order'];
    }

    public function setPaging($paging)
    {
        $this->_param[':paging'] = $paging;

        return $this;
    }

    public function getPaging()
    {
        return $this->_param[':paging'];
    }

    public function setLimit($limit)
    {
        $this->_param[':limit'] = $limit;

        return $this;
    }

    public function getLimit()
    {
        return $this->_param[':limit'];
    }

    public function setOffset($offset)
    {
        $this->_param[':offset'] = $offset;

        return $this;
    }

    public function getOffset()
    {
        return $this->_param[':offset'];
    }

    public function addJoin($join, array $fields = null)
    {
        $this->_param[':join'][] = $join;
        if ($fields !== null) {
            $this->addFields($fields);
        }

        return $this;
    }

    public function addJoins(array $joins, array $fields = null)
    {
        foreach ($joins as $join) {
            $this->addJoin($join);
        }
        if ($fields !== null) {
            $this->addFields($fields);
        }

        return $this;
    }

    public function getJoin()
    {
        return $this->_param[':join'];
    }

    /* read */

    public function select($params = null)
    {
        return $this->setActiveRecordResult($this->_db->fetch($this->_sql($params, true, true)));
    }

    /* write */

    public function save($data = null, $id = null)
    {
        if ($data !== null) {
            $this->setVal($data);
        }
        if ($id !== null) {
            $this->setId($id);
        }

        if ($this->_key === null || $this->{$this->_key} === null) {
            $this->_db->insert($this->_table, $data ?: $this->getVal());
        } else {
            $param = array_merge($this->_param, $param);
            if ($this->{$this->_key} !== null) {
                $param[$this->_key] = $this->{$this->_key};
            }
            $this->_db->update($this->_table, $data, $param);
        }

        return $this;
    }

    public function delete($param = null)
    {
        // only by id
        // $this->_db->delete($this->_table, array_merge($this->_param, $param));

        return $this;
    }

    /* private */

    protected function _sql($param, $select, $from)
    {
        $sql = [];

        if ($select) {
            $sql[':select'] = $this->_field;
            if ($this->_prefix !== null) {
                $sql[':prefix'] = $this->_prefix;
            }
        }

        if ($from) {
            $sql[':from'] = $this->_table;
            if ($this->_alias !== null) {
                $sql[':from'] = [$this->_alias => $this->_table->getName()];
            }
        }

        $sql = array_merge($this->_param, (array) $param, $sql);

        return $this->_db->sql($sql);
    }
}