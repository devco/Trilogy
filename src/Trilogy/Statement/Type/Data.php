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
        //strip null fields
        foreach ($data as $key => $value) {
            if ($value === null) {
                unset($data[$key]);
            }
        }
        
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