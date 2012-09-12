<?php

namespace Test\All;
use Provider\MockConnection;
use Testes\Test\UnitAbstract;
use Trilogy\Connection\Connection;

class Statement extends UnitAbstract
{
    private $db;
    
    public function setUp()
    {
        $this->db = new Connection;
    }
    
    public function simpleSaveInsert()
    {
        $save = (string) $this->db->save->in('test')->data(['field1' => 'value1']);
        $comp = 'INSERT INTO "test" ("field1") VALUES (?)';
        
        $this->assert($save === $comp, 'Compilation failed.');
    }
    
    public function simpleFind()
    {
        $find = (string) $this->db->find->in('test')->where('field1 = ?', 'value1');
        $comp = 'SELECT * FROM "test" WHERE "field1" = ?';
        
        $this->assert($find === $comp, 'Compilation failed.');
    }
    
    public function findJoins()
    {
        $find = (string) $this->db->find->in('a')->where('a.a = ?', 1)->join('b')->where('b.a = a.b');
        $comp = 'SELECT * FROM "a" WHERE "a"."a" = ? INNER JOIN "b" ON "b"."a" = "a"."b"';
        
        $this->assert($find === $comp, 'Compilation failed.');
    }
    
    public function findLike()
    {
        $find = (string) $this->db->find->in('a')->where('a.a ~ %?%', 'b');
        $comp = 'SELECT * FROM "a" WHERE "a"."a" LIKE ?';
        
        $this->assert($find === $comp, 'Compilation failed.');
    }
    
    public function findAndOr()
    {
        $find = (string) $this->db->find->get('a.*')->in(['a', 'b'])->where('a.a', 1)->open()->and('b.a = a.b')->open()->or('a.b')->and('b.a !=')->close(2);
        $comp = 'SELECT "a".* FROM "a", "b" WHERE "a"."a" = ? AND ("b"."a" = "a"."b" OR ("a"."b" IS NULL AND "b"."a" IS NOT NULL))';
        
        $this->assert($find === $comp, 'Compilation failed.');
    }
    
    public function simpleSaveUpdate()
    {
        $save = (string) $this->db->save->in('test')->data(['field1' => 'value2'])->where('field1 = ?', 'value1');
        $comp = 'UPDATE "test" SET "field1" = ? WHERE "field1" = ?';
        
        $this->assert($save === $comp, 'Compilation failed.');
    }
    
    public function simpleRemove()
    {
        $remove = (string) $this->db->remove->in('test')->where('field1 = ?', 'value2');
        $comp   = 'DELETE FROM "test" WHERE "field1" = ?';
        
        $this->assert($remove === $comp, 'Compilation failed.');
    }
    
    public function aliasing()
    {
        $find = (string) $this->db->find->get(['t.*', 't.identifier id'])->in('table t')->and('t.id', 1);
        $comp = 'SELECT "t".*, "t"."identifier" "id" FROM "table" "t" WHERE "t"."id" = ?';
        
        $this->assert($find === $comp, 'Compilation failed.');
    }
}