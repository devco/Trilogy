<?php

namespace Trilogy\Statement;
use Iterator;

/**
 * Iterates over a statement.
 * 
 * @category Iterators
 * @package  Trilogy
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  MIT http://opensource.org/licenses/mit-license.php
 */
class StatementIterator implements Iterator
{
    /**
     * The statement to iterate over.
     * 
     * @var StatementInterface
     */
    private $statement;
    
    /**
     * The current index.
     * 
     * @var int
     */
    private $index = 0;
    
    /**
     * The statement result.
     * 
     * @var mixed
     */
    private $result = [];
    
    /**
     * The number of items in the result.
     * 
     * @var int
     */
    private $count = 0;
    
    /**
     * Whether or not the statement has been executed yet.
     * 
     * @var bool
     */
    private $executed = false;
    
    /**
     * Constructs a new statement iterator.
     * 
     * @param StatementInterface $statement The statement to iterate over.
     * 
     * @return StatementIterator
     */
    public function __construct(StatementInterface $statement)
    {
        $this->statement = $statement;
    }
    
    /**
     * Returns the current item in the iterator.
     * 
     * @return mixed
     */
    public function current()
    {
        return $this->result[$this->index];
    }
    
    /**
     * Returns the current index.
     * 
     * @return int
     */
    public function key()
    {
        return $this->index;
    }
    
    /**
     * Moves on to the next item.
     * 
     * @return void
     */
    public function next()
    {
        ++$this->index;
    }
    
    /**
     * Resets iteration.
     * 
     * @return void
     */
    public function rewind()
    {
        if (!$this->executed) {
            $this->result   = $this->statement->execute();
            $this->count    = count($this->result);
            $this->executed = true;
        }
        
        $this->index = 0;
    }
    
    /**
     * Returns whether or not iteration can proceed.
     * 
     * @return bool
     */
    public function valid()
    {
        return $this->index < $this->count;
    }
}