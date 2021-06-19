<p align="center">
<img width="320" alt="lampager-doctrine2" src="https://user-images.githubusercontent.com/1351893/31839014-3e74b952-b61a-11e7-8d75-adba77a935ae.png">
</p>
<p align="center">
<a href="https://travis-ci.com/lampager/lampager-doctrine2"><img src="https://travis-ci.com/lampager/lampager-doctrine2.svg?branch=master" alt="Build Status"></a>
<a href="https://coveralls.io/github/lampager/lampager-doctrine2?branch=master"><img src="https://coveralls.io/repos/github/lampager/lampager-doctrine2/badge.svg?branch=master" alt="Coverage Status"></a>
<a href="https://scrutinizer-ci.com/g/lampager/lampager-doctrine2/?branch=master"><img src="https://scrutinizer-ci.com/g/lampager/lampager-doctrine2/badges/quality-score.png?b=master" alt="Scrutinizer Code Quality"></a>
</p>

# Lampager for Doctrine 2

Rapid pagination for Doctrine 2

## Requirements

- PHP: ^5.6 || ^7.0 || ^8.0
- [doctrine/orm](https://github.com/doctrine/orm): ^2.4
- [lampager/lampager](https://github.com/lampager/lampager): ^0.4

## Installing

```bash
composer require lampager/lampager-doctrine2
```

## Usage

### Basic

Instantiate your QueryBuilder to create the Paginator.

```php
$cursor = [
    'p.id' => 3,
    'p.createdAt' => '2017-01-10 00:00:00',
    'p.updatedAt' => '2017-01-20 00:00:00',
];

$result = Paginator::create(
    $entityManager
        ->getRepository(Post::class)
        ->createQueryBuilder('p')
        ->where('p.userId = :userId')
        ->setParameter('userId', 1)
)
    ->forward()
    ->setMaxResults(5) // Or ->limit(5)
    ->orderByDesc('p.updatedAt') // ORDER BY p.updatedAt DESC, p.createdAt DESC, p.id DESC
    ->orderByDesc('p.createdAt')
    ->orderByDesc('p.id')
    ->paginate($cursor);
```

It will run the optimized DQL.

```sql
SELECT * FROM App\Entities\Post p
WHERE p.userId = 1
AND (
    p.updatedAt = '2017-01-20 00:00:00' AND p.createdAt = '2017-01-10 00:00:00' AND p.id <= 3
    OR
    p.updatedAt = '2017-01-20 00:00:00' AND p.createdAt < '2017-01-10 00:00:00'
    OR
    p.updatedAt < '2017-01-20 00:00:00'
)
ORDER BY p.updatedAt DESC, p.createdAt DESC, p.id DESC
LIMIT 6
```

And you'll get

```php
object(Lampager\PaginationResult)#X (5) {
  ["records"]=>
  array(5) {
    [0]=>
    object(App\Entities\Post)#X (5) {
      ["id"]=>
      int(3)
      ["userId"]=>
      int(1)
      ["text"]=>
      string(3) "foo"
      ["createdAt"]=>
      object(DateTimeImmutable)#X (3) {
        ["date"]=>
        string(26) "2017-01-10 00:00:00.000000"
        ["timezone_type"]=>
        int(3)
        ["timezone"]=>
        string(3) "UTC"
      }
      ["updatedAt"]=>
      object(DateTimeImmutable)#X (3) {
        ["date"]=>
        string(26) "2017-01-20 00:00:00.000000"
        ["timezone_type"]=>
        int(3)
        ["timezone"]=>
        string(3) "UTC"
      }
    }
    [1]=> ...
    [2]=> ...
    [3]=> ...
    [4]=> ...
  }
  ["hasPrevious"]=>
  bool(false)
  ["previousCursor"]=>
  NULL
  ["hasNext"]=>
  bool(true)
  ["nextCursor"]=>
  array(2) {
    ["p.updatedAt"]=>
    object(DateTimeImmutable)#X (3) {
      ["date"]=>
      string(26) "2017-01-18 00:00:00.000000"
      ["timezone_type"]=>
      int(3)
      ["timezone"]=>
      string(3) "UTC"
    }
    ["p.createdAt"]=>
    object(DateTimeImmutable)#X (3) {
      ["date"]=>
      string(26) "2017-01-14 00:00:00.000000"
      ["timezone_type"]=>
      int(3)
      ["timezone"]=>
      string(3) "UTC"
    }
    ["id"]=>
    int(6)
  }
}
```

### Advanced: Provide mapping for aliased columns and switch to different Hydration Mode

```php
$result = Paginator::create(
    $entityManager
        ->getRepository(Post::class)
        ->createQueryBuilder('p')
        ->select('p.id as postId, p.userId as authorUserId, p.createdAt, p.updatedAt') // Aliasing
        ->where('p.userId = :userId')
        ->setParameter('userId', 1)
)
    ->forward()
    ->setMaxResults(5)
    ->orderByDesc('p.updatedAt')
    ->orderByDesc('p.createdAt')
    ->orderByDesc('p.id')
    ->setMapping([
        'p.id' => 'postId',
        'p.userId' => 'authorUserId',
    ]) // Mapping
    ->paginate($cursor, Query::HYDRATE_ARRAY); // Hydration Mode
```

## Questions

### Seekable(Bidirectional) Query?

Sorry you can't use seekable mode since Doctrine DQL does not support `UNION ALL` syntax. :cry:

### How about [Tuple Comparison](https://www.sql-workbench.eu/comparison/tuple_comparison.html)?

Doctrine DQL does not support Tuple Comparison syntax! :cry:

## Classes

Note: See also [lampager/lampager](https://github.com/lampager/lampager).

| Name | Type | Parent Class | Description |
|:---|:---|:---|:---|
| Lampager\\Doctrine2\\`Paginator` | Class | Lampager\\`Paginator` | Fluent factory implementation for Doctrine 2 |
| Lampager\\Doctrine2\\`Processor` | Class | Lampager\\`AbstractProcessor` | Processor implementation for Doctrine 2 |
| Lampager\\Doctrine2\\`Compiler` | Class | | Compile Lampager Query into Doctrine QueryBuilder |

## API

Note: See also [lampager/lampager](https://github.com/lampager/lampager).

### Paginator::__construct()<br>Paginator::create()

Create a new paginator instance.

```php
static Paginator create(\Doctrine\ORM\QueryBuilder $builder): static
Paginator::__construct(\Doctrine\ORM\QueryBuilder $builder)
```

### Paginator::setMapping()

```php
Paginator::setMapping(string[] $mapping): $this
```

#### Arguments

- **`(string[])`** __*$mapping*__<br> An associative array that contains `$columnNameOrCursorKey => $fetchedFieldName`.
  
### Paginator::setMaxResults()

Alias for `\Lampager\Paginator::limit()`.

```php
Paginator::setMaxResults(int $limit): $this
```

### Paginator::transform()

Transform Lampager Query into Doctrine Query.

```php
Paginator::transform(\Lampager\Query $query): \Doctrine\ORM\Query
```

### Paginator::build()

Perform configure + transform.

```php
Paginator::build(\Lampager\Contracts\Cursor|array $cursor = []): \Doctrine\ORM\Query
```

### Paginator::paginate()

Perform configure + transform + process.

```php
Paginator::paginate(\Lampager\Contracts\Cursor|array $cursor = []): \Lampager\PaginationResult
```

#### Arguments

- **`(mixed)`** __*$cursor*__<br> An associative array that contains `$column => $value` or an object that implements `\Lampager\Contracts\Cursor`. It must be **all-or-nothing**.
    - For initial page, omit this parameter or pass empty array.
    - For subsequent pages, pass all parameters. Partial parameters are not allowd.
