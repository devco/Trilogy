<?php

namespace Trilogy\Statement;

class Expression
{
    private $expr;
    
    public function __construct($expr)
    {
        $this->expr = $expr;
        $this->parse();
    }
    
    public function field()
    {
        return $this->field;
    }
    
    public function operator()
    {
        return $this->operator;
    }
    
    public function value()
    {
        return $this->value;
    }
    
    public function bindable()
    {
        return $this->value === '?';
    }
    
    private function parse()
    {
        $parts = explode(' ', $this->expr);
        
        $this->field    = $parts[0];
        $this->operator = $parts[1];
        $this->value    = $parts[2];
    }
}