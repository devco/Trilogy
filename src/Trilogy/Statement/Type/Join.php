<?php

namespace Trilogy\Statement\Type;
use Trilogy\Statement\Part;

trait Join
{
    use Where;

    /**
     * An instance of the current operating join.
     * 
     * @var Part\Join
     */
    private $join;

	/**
     * The joins on the statement.
     * 
     * @var array
     */
    private $joins = [];

	/**
     * Adds a join to the statement.
     * 
     * @param string $table The table to join.
     * @param string $type  The type of join. Defaults to "inner".
     * 
     * @return StatementAbstract
     */
    public function join($table, $type = 'inner')
    {
        // Set the current join.
        $this->join = new Part\Join($table, $type);

        // Add the join to the list of joins.
        $this->joins[] = $this->join;

        return $this;
    }

    /**
     * Applies an on condition to the current join.
     * 
     * @param string $where  The expression to use.
     * @param mixed  $value  The value to bind to the expression.
     * @param string $concat The concatenator to use: "and" or "or".
     * 
     * @return Join
     */
    public function on($where, $value = null, $concat = 'and')
    {
        // Set the current operating where part.
        $this->where = $where;

        // Ensure the where is an instance of a where part.
        if (!$this->where instanceof Part\Where) {
            $this->where = new Part\Where($this->where, $value, $concat, $this->open);
        }

        // Add the where to the current join.
        $this->join->addWhere($this->where);

        return $this;
    }

    /**
     * Adds an on condition to the current join using "AND".
     * 
     * @param string $where The expression to use.
     * @param mixed  $value The value to bind to the expression.
     * 
     * @return Join
     */
    public function andOn($where, $value = null)
    {
        return $this->on($where, $value, 'and');
    }

    /**
     * Adds an on condition to the current join using "OR".
     * 
     * @param string $where The expression to use.
     * @param mixed  $value The value to bind to the expression.
     * 
     * @return Join
     */
    public function orOn($where, $value = null)
    {
        return $this->on($where, $value, 'or');
    }

	/**
     * Returns the applied joins.
     * 
     * @return array
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * Returns all parameters bound to the where conditions in order of appearance.
     * 
     * @return array
     */
    public function getJoinParams()
    {
        $params = [];

        foreach ($this->joins as $join) {
            foreach ($join->getWheres() as $where) {
                $params[] = $where->getValue();
            }
        }

        return $params;
    }
}