<?php

namespace Trilogy\Statement\Expression;

class Clause extends ExpressionAbstract
{
    const WILDCARD = '*';

    private $field;
    
    private $operator = '=';
    
    private $value = '?';
    
    private $hasWildcardBefore = false;
    
    private $hasWildcardAfter = false;
    
    public function parse($expr)
    {
        $parts = explode(' ', $expr);
        
        $this->field = $parts[0];
        
        if (isset($parts[1])) {
            $this->operator = $parts[1];
        }
        
        if (isset($parts[2])) {
            $this->value = $parts[2];
        }

        $before = self::WILDCARD . '?';
        $after  = '?' . self::WILDCARD;
        $both   = self::WILDCARD . '?' . self::WILDCARD;
        
        $this->hasWildcardBefore = $this->value === $before || $this->value === $both;
        $this->hasWildcardAfter  = $this->value === $after || $this->value === $both;
        
        if ($this->hasWildcardBefore || $this->hasWildcardAfter) {
            $this->value = '?';
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
    
    public function hasWildcardBefore()
    {
        return $this->hasWildcardBefore;
    }
    
    public function hasWildcardAfter()
    {
        return $this->hasWildcardAfter;
    }
    
    public function isBindable()
    {
        return $this->value === '?';
    }
}