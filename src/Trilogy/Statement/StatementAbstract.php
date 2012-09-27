<?php

namespace Trilogy\Statement;
use Trilogy\Connection\ConnectionInterface;

/**
 * Handles common statement functionality.
 * 
 * @category Iterators
 * @package  Trilogy
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  MIT http://opensource.org/licenses/mit-license.php
 */
abstract class StatementAbstract implements StatementInterface
{
    /**
     * The connection.
     * 
     * @var ConnectionInterface
     */
    private $connection;
    
    /**
     * Constructs a new statement.
     * 
     * @param ConnectionInterface $connection The connection to use.
     * 
     * @return StatementAbstract
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Returns the connection associated to the statement.
     * 
     * @return ConnectionInterface
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * Returns the driver associated to the statement.
     * 
     * @return Trilogy\Driver\DriverInterface
     */
    public function driver()
    {
        return $this->connection->driver();
    }
    
    /**
     * Renders the statement as a string.
     * 
     * @return mixed
     */
    public function compile()
    {
        return $this->connection->driver()->compile($this);
    }
    
    /**
     * Executes the statement.
     * 
     * @return mixed
     */
    public function execute()
    {
        return $this->connection->driver()->execute($this);
    }

    /**
     * Returns the parameters associated to the statement.
     * 
     * @return array
     */
    public function params()
    {
        return $this->driver()->getParametersFromStatement($this);
    }
}