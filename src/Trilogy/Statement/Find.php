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
    use Type\Field,
        Type\Join,
        Type\Limit,
        Type\Sort,
        Type\Source;

    /**
     * Executes and returns a single result. If no results are found, false is returned.
     * 
     * @return array | false
     */
    public function one()
    {
        $result = $this->limit(1)->execute();
        
        if (isset($result[0])) {
            return $result[0];
        }
        
        return false;
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
}