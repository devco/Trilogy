<?php

namespace Trilogy\Driver;
use Trilogy\Statement\StatementInterface;

/**
 * Blueprint for a driver.
 * 
 * @category Iterators
 * @package  Trilogy
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  MIT http://opensource.org/licenses/mit-license.php
 */
interface DriverInterface
{
    /**
     * Connects to the data source.
     * 
     * @param array $config The driver configuration.
     * 
     * @return DriverInterface
     */
    public function __construct(array $config);
    
    /**
     * Compiles the statement.
     * 
     * @param StatementInterface $stmt The statement to compile.
     * 
     * @return mixed
     */
    public function compile(StatementInterface $stmt);
    
    /**
     * Executes the specified statement using the specified parameters.
     * 
     * @param StatementInterface $stmt The statement to execute.
     * 
     * @return mixed
     */
    public function execute(StatementInterface $stmt);
    
    /**
     * Returns the raw connection.
     * 
     * @return mixed
     */
    public function raw();

    /**
     * Returns the statement parameters.
     * 
     * @param StatementInterface $stmt The statement to get the parameters from.
     * 
     * @return array
     */
    public function getParametersFromStatement(StatementInterface $stmt);
    
    /**
     * Begins a transaction. returns true if successful, false otherwise 
     * 
     * @return bool 
     */
    public function beginTransaction();
    
    /**
     * Commits the active transaction. return true if successful, false otherwise 
     * 
     * @return bool 
     */
    public function commitTransaction();
    
    /**
     * Perform a rollback on the active transaction. returns true if successful, false otherwise 
     *  
     * @return bool 
     */
    public function rollbackTransaction();
    
    /**
     * Gets the current status of the transaction. true = in transaction, false = no active transaction
     * 
     * @return bool
     */
    public function inTransaction();

    /**
     * Returns the last insert's unique ID.
     *
     * @param string $sequenceName The PostgreSQL sequence name to get the last ID from.
     *
     * @return string
     */
    public function lastInsertId($sequenceName = null);
}