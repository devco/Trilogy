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

    public function subSelectStatement()
    {
        $find1 = $this->db->find->in('test')->where('subId >', 10);
        $find2 = $this->db->find->in('test')->where('catId', 1)->andWhere('id !*', $find1);

        $comp = 'SELECT * FROM "test" WHERE "catId" = ? AND "id" NOT IN (SELECT * FROM "test" WHERE "subId" > ?)';

        $this->assert($find2->compile() == $comp, 'Query does not match');

        $params = $find2->params();

        $this->assert(count($params) == 2, 'Param count should be 2');
        $this->assert($params['0'] == 1, 'Param 0 should be 1');
        $this->assert($params['1'] == 10, 'Param 1 should be 10');
    }
}
