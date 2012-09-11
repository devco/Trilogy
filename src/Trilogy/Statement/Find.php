<?php

namespace Trilogy\Statement;
use IteratorAggregate;

/**
 * Represents a SELECT statement.
 * 
 * @category Iterators
 * @package  Trilogy
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  MIT http://opensource.org/licenses/mit-license.php
 */
class Find extends StatementAbstract implements IteratorAggregate
{
    /**
     * Sort ascending.
     * 
     * @var string
     */
    const ASC = 'ASC';
    
    /**
     * Sort descending.
     * 
     * @var string
     */
    const DESC = 'DESC';
    
    /**
     * The fields to return.
     * 
     * @var array
     */
    private $fields = [];
    
    /**
     * The fields to sort by.
     * 
     * @var array
     */
    private $sortFields = [];
    
    /**
     * The sort direction.
     * 
     * @var string
     */
    private $sortDirection = self::ASC;
    
    /**
     * Executes the statement.
     * 
     * @return mixed
     */
    public function all()
    {
        return $this->connection()->execute($this, $this->getParams());
    }
    
    /**
     * Executes and returns a single result. If no results are found, false is returned.
     * 
     * @return array | false
     */
    public function one()
    {
        $result = $this->limit(1)->all();
        
        if (isset($result[0])) {
            return $result[0];
        }
        
        return false;
    }
    
    /**
     * Sets the fields to find.
     * 
     * @return Find
     */
    public function get($fields)
    {
        if (is_string($fields)) {
            $fields = [$fields];
        }
        
        $this->fields = array_merge($this->fields, $fields);
        $this->fields = array_unique($this->fields);
        
        return $this;
    }
    
    /**
     * Sets the sort direction.
     * 
     * @param string $direction The sort direction.
     * 
     * @return Find
     */
    public function sort($direction)
    {
        $this->sortDirection = strtoupper($direction) === self::ASC ? self::ASC : self::DESC;
        return $this;
    }
    
    /**
     * Sets the sort fields.
     * 
     * @param mixed $fields A string or array of fields to sort by.
     * 
     * @return Find
     */
    public function by($fields)
    {
        if (is_string($fields)) {
            $fields = [$fields];
        }
    
        $this->sortFields = array_merge($this->sortFields, $fields);
        $this->sortFields = array_unique($this->sortFields);
    
        return $this;
    }
    
    /**
     * Returns a statement iterator for the current statement.
     * 
     * @return StatementIterator
     */
    public function getIterator()
    {
        return new StatementIterator($this);
    }
    
    /**
     * Returns the fields to find.
     * 
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }
    
    /**
     * Returns the direction to sort.
     * 
     * @return string
     */
    public function getSortDirection()
    {
        return $this->sortDirection;
    }
    
    /**
     * Returns the fields to sort by.
     * 
     * @return array
     */
    public function getSortFields()
    {
        return $this->sortFields;
    }
}