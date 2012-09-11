<?php

namespace Trilogy\Statement;

class Expression
{
    private $field;
    
    private $operator = '=';
    
    private $value = '?';
    
    private $boundValue;
    
    private $hasWildcardBefore = false;
    
    private $hasWildcardAfter = false;
    
    public function __construct($expr = null)
    {
        if ($expr) {
            $this->parse($expr);
        }
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
    
    public function getOperator()
    {
        return $this->operator;
    }
    
    public function setOperator($operator)
    {
        $this->operator = $operator;
        return $this;
    }
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    
    public function getBoundValue()
    {
        return $this->boundValue;
    }
    
    public function setBoundValue($value)
    {
        $this->boundValue = $value;
        return $this;
    }
    
    public function isBindable()
    {
        return $this->value === '?';
    }
    
    private function parse($expr)
    {
        $parts = explode(' ', $expr);
        
        $this->field = $parts[0];
        
        if (isset($parts[1])) {
            $this->operator = $parts[1];
        }
        
        if (isset($parts[2])) {
            $this->value = $parts[2];
        }
        
        $this->hasWildcardBefore = $this->value === '%?' || $this->value === '%?%';
        $this->hasWildcardAfter  = $this->value === '?%' || $this->value === '%?%';
        
        if ($this->hasWildcardBefore || $this->hasWildcardAfter) {
            $this->value = '?';
        }
    }
}