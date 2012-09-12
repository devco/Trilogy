<?php

namespace Trilogy\Driver;

/**
 * SQL driver blueprint.
 * 
 * @category Iterators
 * @package  Trilogy
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  MIT http://opensource.org/licenses/mit-license.php
 */
interface SqlDriverInterface extends DriverInterface
{
    /**
     * Quotes the specified identifier.
     * 
     * @param string $identifier The identifier to quote.
     * 
     * @return string
     */
    public function quote($identifier);
    
    /**
     * Quotes all identifiers in the array and returns them.
     * 
     * @param array $identifiers The identifiers to quote.
     * 
     * @return array
     */
    public function quoteAll(array $identifiers);
}