<?php

namespace Trilogy\Statement\Part;

class Join
{
    const INNER = 'INNER';
    
    const LEFT = 'LEFT';
    
    const CROSS = 'CROSS';
    
    private $type;
    
    private $table;
    
    private $wheres = [];
    
    public function __construct($table, $type = self::INNER)
    {
        $this->setTable($table);
        $this->setType($type);
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    
    public function getTable()
    {
        return $this->table;
    }
    
    public function setTable($table)
    {
        if (!$table instanceof Table) {
            $table = new Table($table);
        }
        
        $this->table = $table;
        
        return $this;
    }
    
    public function addWhere($where)
    {
        if (!$where instanceof Where) {
            $where = new Where($where);
        }
        
        $this->wheres[] = $where;
        
        return $this;
    }
    
    public function getWheres()
    {
        return $this->wheres;
    }
    
    public function getLastWhere()
    {
        return $this->wheres[count($this->wheres) - 1];
    }
}