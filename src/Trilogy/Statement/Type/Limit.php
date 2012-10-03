<?php

namespace Trilogy\Statement\Type;

trait Limit
{
	/**
     * The limit associated to the statement.
     * 
     * @var int
     */
    private $limit = 0;
    
    /**
     * The offset associated to the statement.
     * 
     * @var int
     */
    private $offset = 0;

    /**
     * Limits the number of items the statement affects.
     * 
     * @param int $limit  The limit.
     * @param int $offset The offset.
     * 
     * @return StatementAbstract
     */
    public function limit($limit, $offset = 0)
    {
        $this->limit  = (int) $limit;
        $this->offset = (int) $offset;
        return $this;
    }
    
    /**
     * Limits using pagination, the number of items the statement affects.
     * 
     * @param int $limit The limit.
     * @param int $page  The page.
     * 
     * @return StatementAbstract
     */
    public function page($limit, $page = 1)
    {
        $this->limit  = (int) $limit;
        $this->offset = ($this->limit * (int) $page) - $this->limit;
        return $this;
    }

    /**
     * Returns the applied limit.
     * 
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }
    
    /**
     * Returns the applied offset.
     * 
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }
}