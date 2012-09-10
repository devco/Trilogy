<?php

namespace Trilogy\Driver;
use Trilogy\Statement;

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
    public function __construct(array $config);
    
    public function execute($statement, array $params = []);
    
    public function compileFind(Statement\Find $find);
    
    public function compileSave(Statement\Save $save);
    
    public function compileRemove(Statement\Remove $remove);
}