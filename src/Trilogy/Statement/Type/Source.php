<?php

namespace Trilogy\Statement\Type;
use Trilogy\Statement\Expression;

trait Source
{
    /**
     * The sources associated to the statement.
     * 
     * @var array
     */
    private $sources = [];

    /**
     * Sets which tables are associated to the statement.
     * 
     * @param Expression\Table | array | string $tables The table or tables to affect.
     * 
     * @return Table
     */
    public function in($sources)
    {
        foreach ((array) $sources as $source) {
            $this->sources[] = $source instanceof Expression\Source ? $source : new Expression\Source($source);
        }
        return $this;
    }
    
    /**
     * Returns the affected tables.
     * 
     * @return array
     */
    public function getSources()
    {
        return $this->sources;
    }
}