<?php

namespace Fogio\Db\Table;

class Process
{
    protected $_tasks;
    protected $_method;

    public function __construct($tasks, $method = '__invoke', $params = [])
    {
        $this->_tasks = $tasks;
        $this->_method = $method;
        foreach ($params as $k => $v) {
            $this->$k = $v;
        }
    }

    public function __invoke()
    {
        $task = current($this->_tasks);
        if (!$task) {
            return $this;
        }
        next($this->_tasks);
        $taks->{$this->_method}($this);
        return $this;
    }

}
