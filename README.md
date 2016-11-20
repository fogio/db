
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
        - [Extending table functionality](#extending-table-functionality)
        - [Extending queries, results](#extending-queries-results)
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
- holds infromation about fields, primary key,
- methods for CRUD operations,
- links mechnism for relations
- extensions mechnism for extend table functionality, queries and results

Table can be accessed by getting db property eg.

```php
use Fogio\Db\Db;

$db = new Db();
$db->setPdo(new Pdo('mysql:host=localhost;dbname=test'));
$db->post; // instance of Fogio\Db\Table\Table
```

Db `Fogio\Db\Db` is a container `Fogio\Container\Container` of table `Fogio\Db\Table\Table` services.
If table is not defined in Db, it will be automatically created throught [service factory mechanism](https://github.com/fogio/container#Service-Factory)
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
            'post_id_user',
            'post_id_comment_first',
            'post_id_comment_last',
            'post_title',
        ];
    }
    
}

```

## Table CRUD

## Table extensions

### Extending table functionality

### Extending queries, results
operation like can be exten

### Defined Extensions
