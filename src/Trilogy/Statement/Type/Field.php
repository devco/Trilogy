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
     * Flag whether the fields should be distinct.
     *
     * @var bool
     */
    private $distinct = false;

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

    /**
     * Sets the returned fields to be distinct.
     *
     * @return Find
     */
    public function distinct($value = true)
    {
        $this->distinct = $value;
        return $this;
    }

    /**
     * Returns whether the fields should be distinct.
     *
     * @return bool
     */
    public function getDistinct()
    {
        return $this->distinct;
    }
}