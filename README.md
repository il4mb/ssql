# SSQL - Simple SQL Builder for PHP

[![PHP](https://img.shields.io/badge/PHP-%3E%3D8.0-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/github/license/Il4mb/ssql)](LICENSE)

## Introduction
SSQL is a lightweight SQL query builder for PHP, designed to simplify database interactions by providing an expressive and fluent API. With SSQL, you can build complex queries without writing raw SQL, making your code cleaner and more maintainable.

## Installation

You can install SSQL via Composer:

```sh
composer require il4mb/ssql
```

## Getting Started

### Initialization
Before using SSQL, you need to initialize the database connection:

```php
require_once "vendor/autoload.php";
use Il4mb\SSQL\Database;

Database::init(__DIR__ . "/database.env.php");
$db = Database::getInstance();
```

### Example Query

Here is an example of how you can build a SQL query using SSQL:

```php
use Il4mb\SSQL\Columns\JsonArray;
use Il4mb\SSQL\Queries\Query;
use Il4mb\SSQL\Table;

$query = $db->table("transactions", "T")
    ->select([
        "*",
        new JsonArray([
            "id" => "T.id",
            "name" => "T.name"
        ], "I"),
        Query::SUM("amount")->when("id", 123456)->then("hallo", 0)
    ])
    ->join(
        Table::leftJoin("users", "U")
            ->select(["id", "name", "email"])
            ->on("id", "T.id")
    )
    ->where(
        ["id", "LIKE", "123"],
        ["id", "LIKE", "123"]
    );

$result = $query->all();
```

## Features
- Fluent API for building SQL queries
- Supports **JOIN**, **WHERE**, **SELECT**, and aggregate functions
- JSON handling with `JsonArray`
- Conditional query building with `when()`
- Easy-to-use database initialization
- **Insert Queries**:
  ```php
  $db->table("users")->insert([
      "name" => "John Doe",
      "email" => "john@example.com"
  ]);
  ```
- **Update Queries**:
  ```php
  $db->table("users")
      ->where("id", "=", 1)
      ->update(["name" => "Jane Doe"]);
  ```
- **Delete Queries**:
  ```php
  $db->table("users")
      ->where("id", "=", 1)
      ->delete();
  ```

## Logging
SSQL provides an example logger to help debug your queries:

```php
function logger($text)
{
    file_put_contents(
        "php-error.log",
        is_string($text) ? $text : print_r($text, 1) . "\n",
        FILE_APPEND
    );
}
```

## License
This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contributing
Contributions are welcome! Feel free to open issues or submit pull requests to improve SSQL.

## Author
Developed by [Il4mb](https://github.com/il4mb).

