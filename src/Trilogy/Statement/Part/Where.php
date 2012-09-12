<?php

namespace Trilogy\Statement\Part;
use Trilogy\Statement\Expression\Clause;

class Where
{
    const CONCAT_AND = 'AND';
    
    const CONCAT_OR = 'OR';
    
    private $clause;
    
    private $value;
    
    private $concatenator;
    
    private $openBrackets;
    
    private $closeBrackets;
    
    public function __construct($clause, $value = null, $concat = self::CONCAT_AND, $openBrackets = 0, $closeBrackets = 0)
    {
        $this->setClause($clause);
        $this->setValue($value);
        $this->setConcatenator($concat);
        $this->setOpenBrackets($openBrackets);
        $this->setCloseBrackets($closeBrackets);
    }
    
    public function getClause()
    {
        return $this->clause;
    }
    
    public function setClause($clause)
    {
        if (!$clause instanceof Clause) {
            $this->clause = new Clause($clause);
        }
        
        $this->clause = $clause;
        
        return $this;
    }
    
    public function getValue()
    {
        $value = $this->value;
        
        if ($this->clause->hasWildcardBefore()) {
            $value = '%' . $value;
        }
        
        if ($this->clause->hasWildcardAfter()) {
            $value .= '%';
        }
        
        return $value;
    }
    
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    
    public function getConcatenator()
    {
        return $this->concatenator;
    }
    
    public function setConcatenator($concat)
    {
        $this->concatenator = strtoupper($concat) === self::CONCAT_AND ? self::CONCAT_AND : self::CONCAT_OR;
        return $this;
    }
    
    public function getOpenBrackets()
    {
        return $this->openBrackets;
    }
    
    public function setOpenBrackets($openBrackets)
    {
        $this->openBrackets = $openBrackets;
        return $this;
    }
    
    public function getCloseBrackets()
    {
        return $this->closeBrackets;
    }
    
    public function setCloseBrackets($closeBrackets)
    {
        $this->closeBrackets = $closeBrackets;
        return $this;
    }
}