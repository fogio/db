<!-- TOC depthFrom:1 depthTo:6 withLinks:1 updateOnSave:1 orderedList:0 -->

- [Fogio Db](#fogio-db)
	- [Instalation](#instalation)
	- [Configuration](#configuration)
	- [FDQ - Fogio Db Query](#fdq-fogio-db-query)
	- [Read](#read)
		- [`fetchAll($fdq)`](#fetchallfdq)
		- [`fetch($fdq)`](#fetchfdq)
		- [`fetchCol($fdq)`](#fetchcolfdq)
		- [`fetchVal($fdq)`](#fetchvalfdq)
		- [`fetchKeyPair($fdq)`](#fetchkeypairfdq)
		- [`fetchKeyed($fdq)`](#fetchkeyedfdq)
		- [`count($fdq, [$expr = '*'])`](#countfdq-expr-)
	- [Create](#create)
	- [Update](#update)
	- [Delete](#delete)
	- [Models](#models)
		- [Definition](#definition)
		- [Active Record](#active-record)

<!-- /TOC -->

# Fogio Db

Pdo wrapper; individual pdo for read and write; FDQ - Fogio DB Query; models; active record;

## Instalation

```
composer require fogio/db
`
```

## Configuration

```php
<?php

use Fogio\Db\Db;

$db = new Db();
$db->setPdo(new Pdo('mysql:host=localhost;dbname=test'));

// or lazy load
$db->setPdoFactory(function(){ return new Pdo('...'); });

// or read & write pdo connections
$db->setPdoRead(new Pdo())->setPdoWrite(new Pdo());

// or read & write lazy
$db
    ->setPdoReadFactory(function(){ return new Pdo('...'); })
    ->setPdoWriteFactory(function(){ return new Pdo('...'); })
;
```

## FDQ - Fogio Db Query

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

## Read

`$db` is an instace of `Fogio\Db\Db`. We've got example data:

```
+----------------------------------+
|               news               |
+---------+------------+-----------+
| news_id | news_title | news_text |
+---------+------------+-----------+
| 1       | Aaa        | Aaa aaa   |
| 2       | Bbb        | Bbb bbb   |
| 3       | Ccc        | Ccc ccc   |
| 4       | Ddd        | Ddd ddd   |
| 5       | Eee        | Eee eee   |
+---------+------------+-----------+
```

### `fetchAll($fdq)`

Returns an array containing all of the result set rows.
Same as `PDOStatement->fetchAll(PDO::FETCH_ASSOC)`, see <http://php.net/manual/en/pdostatement.fetchall.php>

```php
<php

print_r($db->fetchAll([':select' => '|*', ':from' => 'news', ':limit' => 2]));
// output:
Array
(
    [0] => Array
        (
            [news_id] => 1
            [news_title] => Aaa
            [news_text] => Aaa aaa
        )
    [1] => Array
        (
            [news_id] => 2
            [news_title] => Bbb
            [news_text] => Bbb bbb
        )
)
```

### `fetch($fdq)`

Returns first row.
Same as `PDOStatement->fetch(PDO::FETCH_ASSOC)`, see <http://php.net/manual/en/pdostatement.fetch.php>

```php
<php

print_r($db->fetch([':select' => '|*', ':from' => 'news', 'news_id' => 1]));
// output:
Array
(
    [news_id] => 1
    [news_title] => Aaa
    [news_text] => Aaa aaa
)
```

### `fetchCol($fdq)`

Returns first column from result.

```php
<php

print_r($db->fetchCol([':select' => 'news_title', ':from' => 'news', ':limit' => 2]));
// output:
Array
(
    [0] => Aaa
    [1] => Bbb
)
```

### `fetchVal($fdq)`

Returns first column from firm row from result.

```php
<php

print_r($db->fetchVal([':select' => 'news_title', ':from' => 'news', 'news_id' => 1]));
// output:
Aaa
```

### `fetchKeyPair($fdq)`

Returns array with keys from first column and values from second column. Same as `PDOStatement->fetchAll(\PDO::FETCH_KEY_PAIR)`, see <http://php.net/manual/en/pdostatement.fetchall.php>

```php
<php

print_r($db->fetchKeyPair([':select' => 'news_id, news_title', ':from' => 'news', ':limit' => 2]));
// output:
Array
(
    [1] => Aaa
    [2] => Bbb
)
```

### `fetchKeyed($fdq)`

Returns an array containing all of the result set rows. Rows are keyed with value of first column of each row.
Same as `PDOStatement->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC)`, see <http://php.net/manual/en/pdostatement.fetchall.php>

```php
<php

print_r($db->fetchAll([':select' => '|*', ':from' => 'news', ':limit' => 2]));
// output:
Array
(
    [1] => Array
        (
            [news_id] => 1
            [news_title] => Aaa
            [news_text] => Aaa aaa
        )
    [2] => Array
        (
            [news_id] => 2
            [news_title] => Bbb
            [news_text] => Bbb bbb
        )
)
```

### `count($fdq, [$expr = '*'])`

Return count function result for given query

```php
<php

print_r($db->count([':from' => 'news', 'post_id' => 2]));
// same as `SELECT COUNT(*) FROM news WHERE post_id => '2'`
// output:
1
```

## Create

## Update

## Delete

## Models

### Definition

### Active Record
