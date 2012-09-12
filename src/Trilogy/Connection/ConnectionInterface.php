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
     * Returns the driver instance.
     * 
     * @return Trilogy\Driver\DriverInterface;
     */
    public function driver();
}