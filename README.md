Trilogy
=======

Simple, lightweight SQL statement abstraction.

Why
---

To abstract common operations against different backends.

Usage
-----

The goal of Trilogy is to be as simple as possible to use and extend.

### Drivers

Trilogy currently supports the following drivers:

* Mysql
* Pgsql

There are other drivers in the works:

* Mongo
* Mssql

### Making a Connection

In order to make a connection using a particular driver, you just use the main `Connection` object.

    $db = new Trilogy\Connection\Connection;

This will connect you to MySQL on `localhost` using port `3306` and connecting to the database `default`. These are probably the best default, but you'll more than likely want to specify other options:

    $db = new Trilogy\Connection\Connection([
        'driver'   => 'pgsql',
        'host'     => '10.0.0.2',
        'port'     => 5432,
        'database' => 'mydb',
        'username' => 'myusername',
        'password' => 'mypassword',
        'options'  => []
    ]);

#### Driver Options

* `driver` The Trilogy supported driver name to use.
** `mysql`
** `pgsql`
* `host` The host the database resides on.
* `port` The host port.
* `database` The name of the database to use.
* `username` The database username.
* `password` The database password.
* `options` Any driver specific options to use.

### Statements

Trilogy supports the following types of statements:

* Find
* Remove
* Save

### Statement Types

There are different types that make up a complete Trilogy statement.

* `Data` Allows arbitrary data to be applied such as in an INSERT or UPDATE statement.
* `Field` Allows fields to be selected like in a SELECT statement.
* `Join` Allows joins to be applied like in a SELECT statement.
* `Limit` Allows a statement to limit items in a result.
* `Sort` Allows a statement to sort items in a result.
* `Source` Allows a data source such as a table or collection to be selected.
* `Where` Allows conditions to be applied.

### Data Statement Type

The data part allows the application of arbitrary data.

    $save = $db->save->data(['field1' => 'value1', 'field2' => 'value2']);

    // [ 'field1' => 'value1', 'field2' => 'value2' ]
    $save->getData();


### Field Statement Type

The `Field` part allows fields to be selected.

    $find = $db->find
        ->get('test1')
        ->get(['test2', 'test3']);

    // [ 'test1', 'test2', 'test3' ]
    $find->getFields();

### Join Statement Type

    $find = $db->find
        ->join('test2')
        ->on('test1.id = test2.id')
        ->open()
        ->andOn('test2.something', 1)
        ->orOn('test2.something', null)
        ->close();

    // [ Trilogy\Statement\Part\Join, Trilogy\Statement\Part\Join, Trilogy\Statement\Part\Join ]
    $find->getJoins();

    // [ 1, null ]
    $find->getJoinParams();

### Limit Statement Type

    $find = $db->find->limit(10, 20);

    // same as...
    $find = $db->find->page(10, 3);

    // 10
    $find->getLimit();

    // 20
    $find->getOffset();

    // [10, 20]
    $find->getLimitParams();

### Sort Statement Type

    $find = $db->find->sort('field1', 'desc');

    // [ 'field1' => 'DESC' ]
    $find->getSorts();

    // [ 'field1', 'DESC' ]
    $find->getSortParams();

### Source Statement Type

    $find = $db->find
        ->in('table1')
        ->in(['table2', 'table3']);

    // [ 'table1', 'table2', 'table3' ]
    $find->getSources();

### Where Statement Type

    $find = $db->find
        ->where('field1', 1)
        ->open()
        ->andWhere('field2', 2)
        ->orWhere('field3', 3)
        ->close();

    // [ Trilogy\Statement\Part\Where, Trilogy\Statement\Part\Where, Trilogy\Statement\Part\Where ]
    $find->getWheres();

    // [ 1, 2, 3 ]
    $find->getWhereParams();

### Source Expressions

A source expression is broken down into two parts delimitted by whitespace:

    <source> <alias = null>

### Field Expressions

A field expression is broken down into two parts delimitted by whitespace:

    <field> <alias = null>

### Join and Where Expressions

Expressions are broken down into 3 parts delimitted by whitespace:

    <field or alias> <operator = "="> <value = "?">

A `field` is just a field name. Nothing special. An `operator` can is a special token that is used by the driver to convert to a valid operator for the backend it represents. The `value` can either be another field reference, or a placeholder ( ? ).

*Any source, field, alias or non-placeholder value is automatically quoted.*

Valid operators are:

* `=` Equals a value.
* `!=` Not equal to a value.
* `~` Like a value.
* '!~' Not like a value.
* `*` This field must contain one of the values in the specified array.
* `!*` This field must NOT contain one of the values in the specified array.
* `<` Less than.
* '<=' Less than or equal to.
* '>' Greater than.
* '>=' Greater than or equal to.

*Since operators are just passed through if no matching one is found, other operators may work but won't be abstracted by Trilogy.*

Each type of statement uses these parts:

* `Find` uses `Field`, `Join`, `Limit`, `Sort`, `Source`
* `Remove` uses `Source`, `Where`
* `Save` uses `Data`, `Source`, `Where`

### Find Statement

The `Find` statement exists to return a result set.

    $find = $this->db->find->in('test')->where('field1', 'value1');

You can simply output the comiled statement:

    echo $find->compile();

String conversion also works:

    echo $find;

You can interate over a statement before it is even executed. Doing this executes the statement and returns an iterator results:

    foreach ($find as $item) {
        ...
    }

If you need to manually execute the statement:

    $result = $find->execute();

### Remove Statement

The `Remove` statement allows you to create a query that removes records from a data source.

    $removed = $this->db->remove->in('test')->where('field1', 'value1')->execute();

### Save Statement

The `Save` statement allows you to insert or update data. What determines whether or not it inserts or updates depends on if any conditions are given. If not, it inserts; if so, it updates.

Inserting:

    $inserted = $this->db->save->in('test')->data(['field1' => 'value1']);

Updating:

    $updated = $this->db->save->in('test')->data(['field1' => 'value2'])->where('field1', 'value1');

