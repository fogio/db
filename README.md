
# Fogio Db

Pdo wrapper; ORM; FDQ - Fogio DB Query; fast; customizable;

## Instalation

```
composer require fogio/db
```

## Configuration

```php
<?php

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

Fetch

- `fetchAll($fdq)` - Returns an array containing all of the result set rows
- `fetch($fdq)` - returns first row

more [Fetch](docs/Fetch.md) 

Insert

- `insert($table, array $row)` - Inserts row
- `insertAll($table, array $rows)` - inserts many rows in one query

more [Insert](docs/Insert.md) 

Update

- `update($table, array $data, array $fdq)` - Updates rows. Sets data for rows matchew with fdq 

more [Update](docs/Update.md) 

Delete

- `delete($table, array $fdq)` - updates rows 

more [Delete](docs/Delete.md) 

## Transactions

- `beginTransaction()` - Initiates a transaction
- `commit()` - Commits a transaction
- `rollBack()` - Rolls back a transaction

## Table

