<?php

use Il4mb\SSQL\Columns\JsonArray;
use Il4mb\SSQL\Database;
use Il4mb\SSQL\Query;
use Il4mb\SSQL\Cores\Table;
use Doctrine\SqlFormatter\SqlFormatter as SqlFormatterSqlFormatter;

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

$formatter = new SqlFormatterSqlFormatter(null);

Database::init(__DIR__ . "/database.env.php");
$db = Database::getInstance();

$query = $db->with("top_users")->select(["users", "U"], ["name", "id"])->where("id", ">", 123);

echo $formatter->format($query->toQuery());

exit();



$query = $db->table("transactions", "T")
    ->select([
        "*",
        Query::jsonArray([
            "id" => "T.id",
            "name" => "T.name"
        ], "I")
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

$SQL = "";
