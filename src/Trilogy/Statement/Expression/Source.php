<?php

namespace Trilogy\Statement\Expression;

class Source extends ExpressionAbstract
{
    private $source;
    
    private $alias;
    
    public function parse($expr)
    {
        $parts = explode(' ', $expr);
        
        if (count($parts) > 2) {
            throw new LogicException(sprintf(
                'Parse error in expression "%s": Too much whitespace. "In" expressions may only contain a single'
                . 'space separating the source and alias.',
                $expr
            ));
        }
        
        $this->source = $parts[0];
        
        if (isset($parts[1])) {
            $this->alias = $parts[1];
        }
        
        return $this;
    }
    
    public function getSource()
    {
        return $this->source;
    }
    
    public function setSource($source)
    {
        $this->source = $source;
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