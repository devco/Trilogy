<?php

namespace Trilogy\Connection;

/**
 * Connection blueprint.
 * 
 * @category Iterators
 * @package  Trilogy
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  MIT http://opensource.org/licenses/mit-license.php
 */
interface ConnectionInterface
{
    /**
     * Constructs a new connection.
     * 
     * @param array $config The connection configuration.
     * 
     * @return ConnectionInterface
     */
    public function __construct(array $config = []);
    
    /**
     * Prepares and executes the statement and returns the result.
     * 
     * @param mixed $statement The statement to execute.
     * @param array $params    The parameters to execute the statement with.
     * 
     * @return mixed
     */
    public function execute($statement, array $params = []);
}