<?php

namespace Test\All;
use Testes\Test\UnitAbstract;
use Trilogy\Connection\Connection;

class Mysql extends UnitAbstract
{
    private $db;
    
    public function setUp()
    {
        $this->db = new Connection(['driver' => 'mysql']);
    }
    
    public function limit()
    {
        $find = $this->db->find->in('test')->limit(10)->compile();
        $comp = 'SELECT * FROM "test" LIMIT 10';
        
        $this->assert($find === $comp);
    }
    
    public function limitOffset()
    {
        $find = $this->db->find->in('test')->limit(10, 30)->compile();
        $comp = 'SELECT * FROM "test" LIMIT 10, 30';
        
        $this->assert($find === $comp);
    }
    
    public function limitPage()
    {
        $find = $this->db->find->in('test')->page(10, 3)->compile();
        $comp = 'SELECT * FROM "test" LIMIT 10, 20';
        
        $this->assert($find === $comp);
    }
}