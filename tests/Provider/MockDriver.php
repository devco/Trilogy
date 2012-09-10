<?php

namespace Provider;
use Trilogy\Driver\Mysql;

class MockDriver extends Mysql
{
    public function __construct(array $config)
    {
        
    }
    
    public function execute($statement, array $params = [])
    {
        
    }
}