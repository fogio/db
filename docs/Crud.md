
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

# Read

## fetchAll

`fetchAll($fdq)` returns an array containing all of the result set rows.
Same as `PDOStatement->fetchAll(PDO::FETCH_ASSOC)`, see http://php.net/manual/en/pdostatement.fetchall.php

```php
<?php

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

## fetch

`fetch($fdq)` returns first row.
Same as `PDOStatement->fetch(PDO::FETCH_ASSOC)`, see <http://php.net/manual/en/pdostatement.fetch.php>

```php
<?php

print_r($db->fetch([':select' => '|*', ':from' => 'news', 'news_id' => 1]));
// output:
Array
(
    [news_id] => 1
    [news_title] => Aaa
    [news_text] => Aaa aaa
)
```

## fetchCol

`fetchCol($fdq)` returns first column from result.

```php
<?php

print_r($db->fetchCol([':select' => 'news_title', ':from' => 'news', ':limit' => 2]));
// output:
Array
(
    [0] => Aaa
    [1] => Bbb
)
```

## fetchVal

`fetchVal($fdq)` returns first column from firm row from result.

```php
<?php

print_r($db->fetchVal([':select' => 'news_title', ':from' => 'news', 'news_id' => 1]));
// output:
Aaa
```

## fetchKeyPair

`fetchKeyPair($fdq)` returns array with keys from first column and values from second column. Same as `PDOStatement->fetchAll(\PDO::FETCH_KEY_PAIR)`, see <http://php.net/manual/en/pdostatement.fetchall.php>

```php
<?php

print_r($db->fetchKeyPair([':select' => 'news_id, news_title', ':from' => 'news', ':limit' => 2]));
// output:
Array
(
    [1] => Aaa
    [2] => Bbb
)
```

## fetchKeyed

`fetchKeyed($fdq)` returns an array containing all of the result set rows. Rows are keyed with value of first column of each row.
Same as `PDOStatement->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC)`, see <http://php.net/manual/en/pdostatement.fetchall.php>

```php
<?php

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

## count

`count($fdq, [$expr = '*'])` return count function result for given query

```php
<?php

print_r($db->count([':from' => 'news', 'post_id' => 2]));
// same as `SELECT COUNT(*) FROM news WHERE post_id => '2'`
// output:
1
```
