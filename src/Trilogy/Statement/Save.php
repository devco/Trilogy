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
    /**
     * The data bound
     */
    private $data = [];
    
    /**
     * Binds data to the statement.
     * 
     * @return Save
     */
    public function data(array $data)
    {
        $this->data = $data;
        return $this;
    }
    
    /**
     * Returns the bound data.
     * 
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Returns the affected fields.
     * 
     * @return array
     */
    public function getFields()
    {
        return array_keys($this->data);
    }
    
    /**
     * Returns the parameter values bound to the condition expressions.
     * 
     * @return array
     */
    public function getParams()
    {
        $params = [];
        
        foreach ($this->data as $param) {
            $params[] = $param;
        }
        
        foreach (parent::getParams() as $param) {
            $params[] = $param;
        }
        
        return $params;
    }
}