<?php

namespace Test\All;
use Testes\Test\UnitAbstract;
use Trilogy\Connection\Connection;

class Statement extends UnitAbstract
{
    private $db;
    
    public function setUp()
    {
        $this->db = new Connection(['driver' => 'mysql']);
    }
    
    public function simpleSaveInsert()
    {
        $save = $this->db->save->in('test')->data(['field1' => 'value1'])->compile();
        $comp = 'INSERT INTO "test" ("field1") VALUES (?)';
        
        $this->assert($save === $comp, 'Compilation failed.');
    }
    
    public function simpleFind()
    {
        $find = $this->db->find->in('test')->where('field1 = ?', 'value1')->compile();
        $comp = 'SELECT * FROM "test" WHERE "field1" = ?';
        
        $this->assert($find === $comp, 'Compilation failed.');
    }
    
    public function findJoins()
    {
        $find = $this->db->find->in('a')->where('a.a = ?', 1)->join('b')->on('b.a = a.b')->compile();
        $comp = 'SELECT * FROM "a" WHERE "a"."a" = ? INNER JOIN "b" ON "b"."a" = "a"."b"';
        
        $this->assert($find === $comp, 'Compilation failed.');
    }
    
    public function findLike()
    {
        $find = $this->db->find->in('a')->where('a.a ~ *?*', 'b')->compile();
        $comp = 'SELECT * FROM "a" WHERE "a"."a" LIKE ?';
        
        $this->assert($find === $comp, 'Compilation failed.');
    }
    
    public function findAndOr()
    {
        $find = $this->db->find->get('a.*')->in(['a', 'b'])->where('a.a', 1)->open()->andWhere('b.a = a.b')->open()->orWhere('a.b')->andWhere('b.a !=')->close(2)->compile();
        $comp = 'SELECT "a".* FROM "a", "b" WHERE "a"."a" = ? AND ("b"."a" = "a"."b" OR ("a"."b" IS NULL AND "b"."a" IS NOT NULL))';
        
        $this->assert($find === $comp, 'Compilation failed.');
    }
    
    public function simpleSaveUpdate()
    {
        $save = $this->db->save->in('test')->data(['field1' => 'value2'])->where('field1', 'value1')->compile();
        $comp = 'UPDATE "test" SET "field1" = ? WHERE "field1" = ?';
        
        $this->assert($save === $comp, 'Compilation failed.');
    }
    
    public function simpleRemove()
    {
        $remove = $this->db->remove->in('test')->where('field1 = ?', 'value2')->compile();
        $comp   = 'DELETE FROM "test" WHERE "field1" = ?';
        
        $this->assert($remove === $comp, 'Compilation failed.');
    }
    
    public function aliasing()
    {
        $find = $this->db->find->get(['t.*', 't.identifier id'])->in('table t')->where('t.id', 1)->compile();
        $comp = 'SELECT "t".*, "t"."identifier" "id" FROM "table" "t" WHERE "t"."id" = ?';
        
        $this->assert($find === $comp, 'Compilation failed.');
    }

    public function limitParamsIfLimitSpecified()
    {
        $find = $this->db->find->in('table t')->page(10, 2);

        $this->assert($find->params()[0] === 10 && $find->params()[1] === 10);
    }

    public function noLimitParamsIfNoLimitSpecified()
    {
        $find = $this->db->find->in('table t');

        $this->assert(!$find->params());
    }
}