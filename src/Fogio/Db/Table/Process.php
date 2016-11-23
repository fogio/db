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
        $task = array_shift($this->_tasks);
        if ($task) {
            $taks->{$this->_method}($this);
        }
        
        return $this;
    }

    public function unshift($task)
    {
        array_unshift($this->_tasks, $task);

        return $this; 
    }

    public function push($task)
    {
        $this->_tasks[] = $task;

        return $this; 
    }

}
