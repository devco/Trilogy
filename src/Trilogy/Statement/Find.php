<?php

namespace Trilogy\Statement;
use IteratorAggregate;
use Trilogy\Statement\Expression\Field;

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
    private $sorts = [];
    
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
        foreach ((array) $fields as $field) {
            $this->fields[] = $field instanceof Field ? $field : new Field($field);
        }
        return $this;
    }
    
    /**
     * Sets the sort direction.
     * 
     * @param string $field     The field to sort by.
     * @param string $direction The sort direction.
     * 
     * @return Find
     */
    public function sort($field, $direction = self::ASC)
    {
        $this->sorts[$field] = strtoupper($direction) === self::ASC ? self::ASC : self::DESC;
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
     * Returns the sorting fields and directions.
     * 
     * @return string
     */
    public function getSorts()
    {
        return $this->sorts;
    }
}