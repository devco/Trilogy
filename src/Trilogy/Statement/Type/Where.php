<?php

namespace Trilogy\Statement\Type;
use Trilogy\Statement\Part;

trait Where
{
    /**
     * The current operating where part.
     * 
     * @var Part\Where;
     */
    private $where;

    /**
     * List of conditions.
     * 
     * @var array
     */
    private $wheres = [];

    /**
     * The number of open brackets.
     * 
     * @var int
     */
    private $open = 0;

    /**
     * Applies a where condition to the current statement.
     * 
     * @param string $where  The expression to use.
     * @param mixed  $value  The value to bind to the expression.
     * @param string $concat The concatenator to use: "and" or "or".
     * 
     * @return Where
     */
    public function where($where, $value = null, $concat = 'and')
    {
        // Handle an array of expressions.
        if (is_array($where)) {
            foreach ($where as $name => $value) {
                $this->where($name, $value);
            }
            return $this;
        }

        // Set the current where part so that we can modify it if need be.
        $this->where = $where;
        
        // Ensure an instance of the Where part.
        if (!$this->where instanceof Part\Where) {
            $this->where = new Part\Where($this->where, $value, $concat, $this->open);
        }
        
        // Add the where to the statement.
        $this->wheres[] = $this->where;
        
        // If we are in brackets, reset.
        if ($this->open) {
            $this->open = 0;
        }
        
        return $this;
    }
    
    /**
     * Applies a where condition to the current mode ("where" or "join").
     * 
     * @param string $where The expression to use.
     * @param mixed  $value The value to bind to the expression.
     * 
     * @return Where
     */
    public function andWhere($where, $value = null)
    {
        return $this->where($where, $value, 'and');
    }
    
    /**
     * Applies a where condition to the current mode ("where" or "join").
     * 
     * @param string $where The expression to use.
     * @param mixed  $value The value to bind to the expression.
     * 
     * @return Where
     */
    public function orWhere($where, $value = null)
    {
        return $this->where($where, $value, 'or');
    }

    /**
     * Opens one or more brackets.
     * 
     * @param int $amt The number of brackets to open.
     * 
     * @return Where
     */
    public function open($amt = 1)
    {
        $this->open += $amt;
        return $this;
    }

    /**
     * Closes one or more brackets.
     * 
     * @param int $amt The number of brackets to close.
     * 
     * @return Where
     */
    public function close($amt = 1)
    {
        $this->where->setCloseBrackets($this->where->getCloseBrackets() + $amt);
        return $this;
    }

    /**
     * Returns the applied conditions.
     * 
     * @return array
     */
    public function getWheres()
    {
        return $this->wheres;
    }
}