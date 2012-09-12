<?php

namespace Trilogy\Statement\Expression;

class Table extends ExpressionAbstract
{
    private $table;
    
    private $alias;
    
    public function parse($expr)
    {
        $parts = explode(' ', $expr);
        
        if (count($parts) > 2) {
            throw new LogicException(sprintf(
                'Parse error in expression "%s": Too much whitespace. Table expressions may only contain a single'
                . 'space separating the table and alias.',
                $expr
            ));
        }
        
        $this->table = $parts[0];
        
        if (isset($parts[1])) {
            $this->alias = $parts[1];
        }
        
        return $this;
    }
    
    public function getTable()
    {
        return $this->table;
    }
    
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }
    
    public function getAlias()
    {
        return $this->alias;
    }
    
    public function setAlias()
    {
        $this->alias = $alias;
        return $this;
    }
}