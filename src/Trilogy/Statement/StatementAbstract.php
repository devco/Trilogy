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
}