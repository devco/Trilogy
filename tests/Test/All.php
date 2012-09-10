<?php

namespace Test;
use Testes\Suite\Suite;
use Trilogy\Autoloader;
use Trilogy\Connection\Connection;

class All extends Suite
{
    public function setUp()
    {
        require_once __DIR__ . '/../../src/Trilogy/Autoloader.php';
        Autoloader::register();
        
        Connection::$defaults['driver'] = 'mock';
        Connection::register('mock', 'Provider\MockDriver');
    }
}