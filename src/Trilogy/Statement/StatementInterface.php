<?php

namespace Trilogy\Statement;
use Trilogy\Connection\ConnectionInterface;

/**
 * Blueprint for a basic statement.
 * 
 * @category Iterators
 * @package  Trilogy
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  MIT http://opensource.org/licenses/mit-license.php
 */
interface StatementInterface
{
    /**
     * Constructs a new statement.
     * 
     * @param ConnectionInterface $connection The connection to use.
     * 
     * @return StatementAbstract
     */
    public function __construct(ConnectionInterface $connection);
    
    /**
     * Renders the statement as a string.
     * 
     * @return mixed
     */
    public function compile();
    
    /**
     * Executes the statement.
     * 
     * @return mixed
     */
    public function execute();
}