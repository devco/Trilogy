<?php

namespace Trilogy\Statement\Expression;

class Field extends ExpressionAbstract
{
    private $field;
    
    private $alias;
    
    public function parse($expr)
    {
        $parts = explode(' ', $expr);
        
        if (count($parts) > 2) {
            throw new LogicException(sprintf(
                'Parse error in expression "%s": Too much whitespace. Field expressions may only contain a single'
                . 'space separating the field and alias.',
                $expr
            ));
        }
        
        $this->field = $parts[0];
        
        if (isset($parts[1])) {
            $this->alias = $parts[1];
        }
        
        return $this;
    }
    
    public function getField()
    {
        return $this->field;
    }
    
    public function setField($field)
    {
        $this->field = $field;
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