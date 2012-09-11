<?php

namespace Test\All;
use Provider\MockConnection;
use Testes\Test\UnitAbstract;
use Trilogy\Connection\Connection;

class Statement extends UnitAbstract
{
    private $connection;
    
    public function setUp()
    {
        $this->connection = new Connection;
    }
    
    public function simpleSaveInsert()
    {
        $save = (string) $this->connection->save->in('test')->data(['field1' => 'value1']);
        $comp = 'INSERT INTO "test" ("field1") VALUES (?)';
        
        $this->assert($save === $comp, 'Compilation failed.');
    }
    
    public function simpleFind()
    {
        $find = (string) $this->connection->find->in('test')->where('field1 = ?', 'value1');
        $comp = 'SELECT * FROM "test" WHERE "field1" = ?';
        
        $this->assert($find === $comp, 'Compilation failed.');
    }
    
    public function findJoins()
    {
        $find = (string) $this->connection->find->in('a')->where('a.a = ?', 1)->join('b')->where('b.a = a.b');
        $comp = 'SELECT * FROM "a" WHERE "a"."a" = ? INNER JOIN "b" ON "b"."a" = "a"."b"';
        
        $this->assert($find === $comp, 'Compilation failed.');
    }
    
    public function findLike()
    {
        $find = (string) $this->connection->find->in('a')->where('a.a ~ %?%', 'b');
        $comp = 'SELECT * FROM "a" WHERE "a"."a" LIKE ?';
        
        $this->assert($find === $comp, 'Compilation failed.');
    }
    
    public function simpleSaveUpdate()
    {
        $save = (string) $this->connection->save->in('test')->data(['field1' => 'value2'])->where('field1 = ?', 'value1');
        $comp = 'UPDATE "test" SET "field1" = ? WHERE "field1" = ?';
        
        $this->assert($save === $comp, 'Compilation failed.');
    }
    
    public function simpleRemove()
    {
        $remove = (string) $this->connection->remove->in('test')->where('field1 = ?', 'value2');
        $comp   = 'DELETE FROM "test" WHERE "field1" = ?';
        
        $this->assert($remove === $comp, 'Compilation failed.');
    }
}