<?php

namespace Trilogy\Statement\Type;

trait Data
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
}