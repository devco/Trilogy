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
        $find = $this->db->find->in('test')->limit(10);
        $comp = 'SELECT * FROM "test" LIMIT ?, ?';
        
        $this->assert($find->compile() === $comp);
        $this->assert($find->getLimit() === 10 && $find->getOffset() === 0);
    }
    
    public function limitOffset()
    {
        $find = $this->db->find->in('test')->limit(10, 30);
        $comp = 'SELECT * FROM "test" LIMIT ?, ?';
        
        $this->assert($find->compile() === $comp);
        $this->assert($find->getLimit() === 10 && $find->getOffset() === 30);
    }
    
    public function limitPage()
    {
        $find = $this->db->find->in('test')->page(10, 3);
        $comp = 'SELECT * FROM "test" LIMIT ?, ?';
        
        $this->assert($find->compile() === $comp);
        $this->assert($find->getLimit() === 10 && $find->getOffset() === 20);
    }

    public function filterBoolean()
    {
        $find = $this->db->find->in('test')->where('x', true);
        $values = $this->db->driver()->getParametersFromStatement($find);

        $this->assert($values[0] === 1, 'The boolean true should have been filtered to the integer 1 ('. var_export($values[0], true) .')');

        $find = $this->db->find->in('test')->where('x', false);
        $values = $this->db->driver()->getParametersFromStatement($find);

        $this->assert($values[0] === 0, 'The boolean true should have been filtered to the integer 0 ('. var_export($values[0], true) .')');
    }
}