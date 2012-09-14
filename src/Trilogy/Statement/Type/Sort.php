<?php

namespace Trilogy\Statement\Type;

trait Sort
{
    /**
     * The fields to sort by.
     * 
     * @var array
     */
    private $sorts = [];

    /**
     * Sets the sort direction.
     * 
     * @param string $field     The field to sort by.
     * @param string $direction The sort direction.
     * 
     * @return Find
     */
    public function sort($field, $direction = 'asc')
    {
        $this->sorts[$field] = strtolower($direction) === 'asc' ? 'ASC' : 'DESC';
        return $this;
    }

    /**
     * Returns the sorting fields and directions.
     * 
     * @return string
     */
    public function getSorts()
    {
        return $this->sorts;
    }

    /**
     * Returns parameters bound to the sort clause.
     * 
     * @return array
     */
    public function getSortParams()
    {
        $params = [];

        foreach ($this->sorts as $name => $direction) {
            $params[] = $name;
            $params[] = $direction;
        }

        return $params;
    }
}