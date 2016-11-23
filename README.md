
<!-- TOC -->

- [Instalation](#instalation)
- [Db](#db)
    - [Configuration](#configuration)
    - [FDQ](#fdq)
    - [CRUD](#crud)
    - [Transactions](#transactions)
- [Table](#table)
    - [Defining Table](#defining-table)
    - [Table CRUD](#table-crud)
    - [Table extensions](#table-extensions)
        - [Extending Table](#extending-table)
        - [Extending CRUD](#extending-crud)
            - [Process params](#process-params)
            - [Extensions call flow](#extensions-call-flow)
        - [Defined Extensions](#defined-extensions)

<!-- /TOC -->

Pdo wrapper; ORM; FDQ - Fogio DB Query; fast; customizable; table extensions; Active Record

# Instalation

```
composer require fogio/db
```

# Db

The database object

## Configuration

```php
use Fogio\Db\Db;

$db = new Db();
$db->setPdo(new Pdo('mysql:host=localhost;dbname=test'));

// or lazy load
$db->setPdoFactory(function(){ return new Pdo('mysql:host=localhost;dbname=test'); });
```

## FDQ

FDQ - Fogio Db Query

FDQ is a string raw sql query or an array. Special params starts with `:`.
Everything else is a WHERE clause. `|` at beging forces raw sql.
Values are escaped. Full featured example:

```php
<?php

$fdq = [
    ':select' => ['post_id', 'title' => 'post_title']  // `post_id`, `post_title` as 'title'
    ':select' => 'post_id, post_title as title' // post_id, post_title as title
    ':select' => ['|count' => '|COUNT(*)']  // COUNT(*) as count
    ':prefix' => 'SQL_NO_CACHE DISTINCT'
    ':from'   => 'post'
    ':from'   => ['p' => 'post']
    ':join'   => ['JOIN author ON (author_id = post_id_author)', 'LEFT JOIN img ON (author_id_img = img_id)']
    ':group'  => 'post_id'
    ':having' => 'post_id > 0'
    ':having' => ['post_id >' =>  '0']
    ':order'  => 'post_published DESC'
    ':paging' => \Fogio\Paging\PagingInterface
    ':limit'  => 10,
    ':offset' => 0,
    'post_level' => [1, 2, 3] // `post_level` IN ('1', '2',  '3')
    'post_level BETWEEN' => [4, 5] // `post_level` BETWEEN '4' AND '5'
    'post_level <>' => 4 // `post_level` <> '4'
    '|post_level <>' => 4 // post_level <> '4'
    "|post_level != '{a}')" => ['{a}' => 4] // post_level != '4'
    ':operator' => 'AND' // values: AND, OR; default: AND; logical operator that joins all conditions
    [':operator' => 'OR', 'post_level' => '1'
        [':operator' => 'OR', 'post_level' => '2', 'post_level' => '3']
    ], // post_level = '1' OR (post_level = '2' OR  post_level = '3')
];
```

## CRUD

- `fetchAll($fdq)` - Returns an array containing all of the result set rows
- `fetch($fdq)` - Returns first row
- `insert($table, array $row)` - Inserts row
- `insertAll($table, array $rows)` - Inserts many rows in one query
- `update($table, array $data, array $fdq)` - Updates rows. Sets data for rows matched with fdq
- `delete($table, array $fdq)` - Deletes rows

[more](docs/Crud.md)


## Transactions

- `beginTransaction()` - Initiates a transaction
- `commit()` - Commits a transaction
- `rollBack()` - Rolls back a transaction

# Table

Table represents the table in database. Features:
- infromation about fields, primary key,
- CRUD operations,
- links mechnism for relations,
- extensions mechnism for extending table functionality, queries and results.

Table can be accessed by getting db property eg.

```php
use Fogio\Db\Db;

$db = new Db();
$db->setPdo(new Pdo('mysql:host=localhost;dbname=test'));
$db->post; // instance of Fogio\Db\Table\Table
```

Db `Fogio\Db\Db` is a container `Fogio\Container\Container` of table `Fogio\Db\Table\Table` services.
If table is not defined in Db, it will be automatically created throught [service factory mechanism](https://github.com/fogio/container#factory).
Db will read schema using `SHOW TABLES` and `SELECT COLUMNS FROM table`.
If you go to production or you want customize table, define table in db.
 
## Defining Table 
 
```php
use Fogio\Db\Db;

$db = new Db();
$db->setPdo(new Pdo('mysql:host=localhost;dbname=test'));
$db(['post' => Post::class]);

class Post extends Table 
{

    protected function provideName() 
    {
        return 'post';
    }

    protected function provideKey() 
    {
        return 'post_id';
    }

    protected function provideFields() 
    {
        return [
            'post_id',
            'post_insert',
            'post_title',
            'post_order',
            'post_attr',
        ];
    }
    
}

```

## Table CRUD

- `fetchAll($fdq)` - Returns an array containing all of the result set rows
- `fetch($fdq)` - Returns first row
- `insert(array $row)` - Inserts row
- `insertAll(array $rows)` - Inserts many rows in one query
- `update(array $data, array $fdq)` - Updates rows. Sets data for rows matched with fdq
- `delete(array $fdq)` - Deletes rows


## Table extensions

Extensions are defined by `setExtensions` method or by lazy load mechanism in `provideExtensions` method:

```php
class Post extends Table 
{
    protected function provideExtensions()
    {
        return [
           (new DefaultOrder)->setOrder('post_id DESC'),
           (new SerializeFields)->setFields(['post_attr']),
           new InsertTime()
           new Vcs(),
        ];
    }
}

```

### Extending Table

Table is also a container. If extension implments `OnExtendInterface`, method `onExtend(Table $table)` will be called to initialize new functionality.
Eg.
 
```php
use Fogio\Db\Db;
use Fogio\Db\Table\Table;
use Fogio\Db\Table\OnExtendInterface;
use Fogio\Container\InvokableInterface;

class Save implements OnExtendInterface, InvokableInterface
{
    protected $table;

    public function onExtend(Table $table)
    {
        $this->table = $table(['save' => $this]);
        $this->key = $this->table->getKey();
        if ($this->key === null) {
            throw new LogicException();
        }
    }

    public function invoke($row)
    {
        return $this->save($row);
    }

    public function save($row)
    {
        if ($row[$this->key] === null) {
            return $this->table->insert($row);
        } else {
            $fdq = [$this->key => $row[$this->key]];
            unset($row[$this->key]);
            return $this->table->update($row, $fdq);
        }
    }

}

$db = new Db();
$db->setPdo(new Pdo('mysql:host=localhost;dbname=test'));
$db(['post' => Post::class]);
print_r($db->post->save());

```

More about [InvokableInterface](https://github.com/fogio/container#invoke)


### Extending CRUD

CRUD operations can be extended by implmenting interface in extensions class.

```php
namespace Fogio\Db\Table;

interface On<operation>Interface
{
    public function on<operation>(Process $process);

}

<operation> = Fetch|FetchAll|Insert|InsertAll|Update|Delete
```
Eg.
```php
use Fogio\Db\Table\Table;
use Fogio\Db\Table\OnFetchAllInterface;
use Fogio\Db\Table\Process;

class DefaultOrder implements OnFetchAllInterface
{
    protected $order;

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function onFetchAll(Process $process)
    {
        if (!isset($process->fdq[':order'])) {
            if ($this->order === null) {
                $this->order = "`{$process->table->getKey()}` ASC";
            }
            $process->fdq[':order'] = $this->order;
        }

        $process();
    }

}

class Post extends Table 
{
    protected function provideExtensions()
    {
        return [
           (new DefaultOrder)->setOrder('post_id DESC'),
        ];
    }
}

$db = new Db();
$db->setPdo(new Pdo('mysql:host=localhost;dbname=test'));
$db(['post' => Post::class]);
$db->post->fetchAll(); 
// will execute query:
// SELECT `post_id`,  `post_insert`, `post_title`, `post_order`, `post_attr` FROM `post` ORDER post_id DESC

```

#### Process params

```php
OnFetchInterface->onFetch(Process $process) 
{
    $process->table; // Fogio\Db\Table\Table
    $process->fdq; // fetch fdq
    $process->result; // fetched record, return value
}
OnFetchAllInterface->onFetchAll(Process $process) 
{
    $process->table; // Fogio\Db\Table\Table
    $process->fdq; // fetch fdq
    $process->result; // fetched records, return value
}
OnInsertInterface->onInsert(Process $process) 
{
    $process->table; // Fogio\Db\Table\Table
    $process->row; // row to insert
    $process->result; // PDOStatement, return value
}
OnInsertAllInterface->onInsertAll(Process $process) 
{
    $process->table; // Fogio\Db\Table\Table
    $process->rows; // rows to insert
    $process->result; // PDOStatement, return value
}
OnUpdateInterface->onUpdate(Process $process) 
{
    $process->table; // Fogio\Db\Table\Table
    $process->data; // data to set
    $process->fdq; // condition 
    $process->result; // PDOStatement, return value
}
OnDeleteInterface->onDelete(Process $process) 
{
    $process->table; // Fogio\Db\Table\Table
    $process->fdq; // condition
    $process->result; // PDOStatement, return value
}
```


#### Extensions call flow

Eg.
```php
use Fogio\Db\Table\OnFetchInterface;

class TestDepth implements OnFetchInterface
{
    protected $name;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function onFetchPre(Process $process)
    {
        // pre
        $process->depth++;
        echo str_repeat(' ', $process->depth)  . $this->name . " - Pre\n";

        if ($this->name === 'C') {
            $process->val = 'Test Return C';
        }

        if ($this->name !== 'C') { // continue process?
            $process();
        }

        // post
        echo str_repeat(' ', $process->depth)  . $this->name . " - Post\n";
        $process->depth--;

        if ($this->name === 'B') {
            $process->val = 'Test Return B';
        }
    }
}

class Post extends Table 
{
    protected function provideExtensions()
    {
        return [
           (new TestDepth)->setName('A'),
           (new TestDepth)->setName('B'),
           (new TestDepth)->setName('C'),
           (new TestDepth)->setName('D'),
        ];
    }
}

$db = new Db();
$db->setPdo(new Pdo('mysql:host=localhost;dbname=test'));
$db(['post' => Post::class]);
echo $db->post->fetch(['limit' => 0]);
// output:
 A - Pre
  B - Pre
   C - Pre
   C - Post
  B - Post 
 A - Post
Test Return B

```

### Defined Extensions
