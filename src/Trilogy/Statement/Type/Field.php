<?php

namespace Trilogy\Statement\Type;
use Trilogy\Statement\Expression;

trait Field
{
    /**
     * The fields to return.
     * 
     * @var array
     */
    private $fields = [];

    /**
     * Sets the fields to find.
     * 
     * @return Find
     */
    public function get($fields)
    {
        foreach ((array) $fields as $field) {
            $this->fields[] = $field instanceof Expression\Field ? $field : new Expression\Field($field);
        }
        return $this;
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
}