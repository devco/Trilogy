<?php

namespace Trilogy\Statement\Part;
use Trilogy\Statement\Expression;

class Join
{
    const INNER = 'INNER';
    
    const LEFT = 'LEFT';
    
    const CROSS = 'CROSS';
    
    private $type;
    
    private $source;
    
    private $wheres = [];
    
    public function __construct($source, $type = self::INNER)
    {
        $this->setSource($source);
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
    
    public function getSource()
    {
        return $this->source;
    }
    
    public function setSource($source)
    {
        if (!$source instanceof Expression\Source) {
            $source = new Expression\Source($source);
        }
        
        $this->source = $source;
        
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