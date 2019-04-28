<?php
error_reporting(E_ALL);
ini_set( 'display_errors','1');

include '../autoloader.php';

use Tweakers\Database\Connection;
use Tweakers\Model\Article;

$host = 'db'; //only works when running in docker
$username = 'tweakers-test';
$password = 'test-tweakers';
$database = 'tweakers';

try {
    $connection = new Connection('db');
    $connection = $connection
        ->setUsername($username)
        ->setPassword($password)
        ->setDb($database)
        ->connect()
    ;
} catch ( \Exception $exception) {
    if ($exception->getCode() == 1045) {
        die('Access denied.');
    }
    die('database connection failed.');
}

$articleId = (int)$_GET['articleId'] ?: 1;

$article = new Article($articleId, $connection);

echo "<pre>";
var_dump($article);
