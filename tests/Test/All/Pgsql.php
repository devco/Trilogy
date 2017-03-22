<?php

namespace Test\All;
use Testes\Test\UnitAbstract;
use Trilogy\Connection\Connection;

class Pgsql extends UnitAbstract
{
    private $db;
    
    public function setUp()
    {
        $this->db = new Connection(['driver' => 'pgsql']);
    }

    public function limit()
    {
        $find = $this->db->find->in('test')->limit(10);
        $comp = 'SELECT * FROM "test" LIMIT ? OFFSET ?';
        
        $this->assert($find->compile() === $comp);
        $this->assert($find->getLimit() === 10 && $find->getOffset() === 0);
    }
    
    public function limitOffset()
    {
        $find = $this->db->find->in('test')->limit(10, 30);
        $comp = 'SELECT * FROM "test" LIMIT ? OFFSET ?';
        
        $this->assert($find->compile() === $comp);
        $this->assert($find->getLimit() === 10 && $find->getOffset() === 30);
    }
    
    public function limitPage()
    {
        $find = $this->db->find->in('test')->page(10, 3);
        $comp = 'SELECT * FROM "test" LIMIT ? OFFSET ?';
        
        $this->assert($find->compile() === $comp);
        $this->assert($find->getLimit() === 10 && $find->getOffset() === 20);
    }

    public function filterBoolean()
    {
        $find = $this->db->find->in('test')->where('x', true);
        $values = $this->db->driver()->getParametersFromStatement($find);

        $this->assert($values[0] === 'TRUE', 'The boolean true should have been filtered to a string ('. var_export($values[0], true) .')');

        $find = $this->db->find->in('test')->where('x', false);
        $values = $this->db->driver()->getParametersFromStatement($find);

        $this->assert($values[0] === 'FALSE', 'The boolean true should have been filtered to a string ('. var_export($values[0], true) .')');
    }

    public function subSelect()
    {
        $find1 = $this->db->find->in('test')->where('id *', "SELECT id from test WHERE subId > 10");
        $find2 = $this->db->find->in('test')->where('id !*', "SELECT id from test WHERE subId > 10");

        $comp1 = 'SELECT * FROM "test" WHERE "id" IN (SELECT id from test WHERE subId > 10)';
        $comp2 = 'SELECT * FROM "test" WHERE "id" NOT IN (SELECT id from test WHERE subId > 10)';

        $this->assert($find1->compile() == $comp1);
        $this->assert($find2->compile() == $comp2);
    }

    public function subSelectStatement()
    {
        $find1 = $this->db->find->in('test')->where('subId >', 10);
        $find2 = $this->db->find->in('test')->where('id !*', $find1);

        $comp2 = 'SELECT * FROM "test" WHERE "id" NOT IN (SELECT * FROM "test" WHERE "subId" > ?)';

        $this->assert($find2->compile() == $comp2);
    }
}
