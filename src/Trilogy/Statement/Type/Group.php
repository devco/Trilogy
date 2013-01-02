<?php

namespace Trilogy\Statement\Type;
use Trilogy\Statement\Expression;

trait Group
{
    /**
     * The fields to group by.
     *
     * @var array
     */
    private $groupByFields = [];

    /**
     * Sets the group by fields.
     *
     * @param mixed $fields  The fields to group by.
     *
     * @return Group
     */
    public function group($fields)
    {
        if (is_array($fields)) {
            $this->groupByFields = $fields;
        }  else {
            $gbFields = explode(",", $fields);

            foreach ($gbFields as $field) {
                $this->groupByFields[] = trim($field);
            }
        }

        return $this;
    }

    /**
     * Returns the group by fields.
     *
     * @return array
     */
    public function getGroupByFields()
    {
        return $this->groupByFields;
    }
}
