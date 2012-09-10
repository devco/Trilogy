<?php

namespace Trilogy\Statement;

/**
 * Represents a INSERT / UPDATE statement.
 * 
 * @category Iterators
 * @package  Trilogy
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  MIT http://opensource.org/licenses/mit-license.php
 */
class Save extends StatementAbstract
{
    private $data = [];
    
    /**
     * Binds data to the statement.
     * 
     * @return Save
     */
    public function data(array $data)
    {
        $this->data = $data;
        
        $temp = [];
        
        foreach ($data as $v) {
            $temp[] = $v;
        }
        
        foreach ($this->getParams() as $v) {
            $temp[] = $v;
        }
        
        $this->setParams($temp);
        
        return $this;
    }
    
    public function getData()
    {
        return $this->data;
    }
}