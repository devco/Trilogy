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
    public function __construct(ConnectionInterface $connection);
    
    public function __toString();
	
	public function execute();
}