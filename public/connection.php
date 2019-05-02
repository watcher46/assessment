<?php

use Tweakers\NestedSet\Adapter\PDOAdapter;

$host = 'db'; //only works when running in docker
$username = 'tweakers-test';
$password = 'test-tweakers';
$database = 'tweakers';
$options = [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING,
];

try {
    $pdoConnection = new PDO(
        "mysql:host={$host};dbname={$database};charset=utf8;",
        $username,
        $password,
        $options
    );
    $adapter = new PDOAdapter($pdoConnection, 'comments');
} catch ( PDOException $exception) {
    if ($exception->getCode() == 1045) {
        die('Access denied.');
    }
    die('database connection failed.');
}
