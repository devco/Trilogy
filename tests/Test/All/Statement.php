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

    public function findNotLike()
    {
        $find = $this->db->find->in('a')->where('a.a !~ *?*', 'b')->compile();
        $comp = 'SELECT * FROM "a" WHERE "a"."a" NOT LIKE ?';
        
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

    public function properParameterOrder()
    {
        $find   = $this->db->find->in('table')->where('something', 1)->limit(10, 0);
        $params = $find->params();

        $this->assert($params[0] === 1 && $params[1] === 10 && $params[2] === 0);
    }

    public function properParameterCount()
    {
        $find = $this->db->find->in('table')->where('something', 1)->limit(10, 0);
        
        $this->assert(count($find->params()) === 3);
    }

    public function operatorIn()
    {
        $find1 = $this->db->find->in('table')->where('something *', 1);
        $find2 = $this->db->find->in('table')->where('something * ?', [1]);
        $comp  = 'SELECT * FROM "table" WHERE "something" IN (?)';

        $this->assert($find1->compile() === $comp, $find1->compile());
        $this->assert($find2->compile() === $comp, $find2->compile());

        $find1 = $this->db->find->in('table')->where('something *', [1, 2, 3]);
        $find2 = $this->db->find->in('table')->where('something * ?', [1, 2, 3]);
        $comp  = 'SELECT * FROM "table" WHERE "something" IN (?, ?, ?)';

        $this->assert($find1->compile() === $comp, $find1->compile());
        $this->assert($find2->compile() === $comp, $find2->compile());
    }

    public function operatorNotIn()
    {
        $find = $this->db->find->in('table')->where('something !* ?', [1, 2, 3]);
        $comp = 'SELECT * FROM "table" WHERE "something" NOT IN (?, ?, ?)';

        $this->assert($find->compile() === $comp, $find->compile());
    }
}