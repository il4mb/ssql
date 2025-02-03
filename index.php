<?php

use Il4mb\Db\Columns\JsonArray;
use Il4mb\Db\Database;
use Il4mb\Db\Queries\Query;
use Il4mb\Db\Table;

ini_set("log_errors", 1);
ini_set("error_log", "php-error.log");
if (file_exists("php-error.log")) {
    unlink("php-error.log");
}

function logger($text)
{
    file_put_contents(
        "php-error.log",
        is_string($text) ? $text : print_r($text, 1) . "\n",
        FILE_APPEND
    );
}

require_once "vendor/autoload.php";

Database::init(__DIR__ . "/database.env.php");
$db = Database::getInstance();

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

$query->all();
