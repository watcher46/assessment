<?php
error_reporting(E_ALL);
ini_set( 'display_errors','1');

include '../autoloader.php';

use Tweakers\Model\Article;
use Tweakers\Model\User;

$host = 'db'; //only works when running in docker
$username = 'tweakers-test';
$password = 'test-tweakers';
$database = 'tweakers';

try {
    $connection = new PDO(
        "mysql:host={$host};dbname={$database};charset=utf8;",
        $username,
        $password
    );
} catch ( PDOException $exception) {
    if ($exception->getCode() == 1045) {
        die('Access denied.');
    }
    die('database connection failed.');
}

$articleId = (int)$_GET['articleId'] ?: 1;

$article = new Article($articleId, $connection);
$user = new User(1, $connection);

echo "<pre>";
var_dump($article, $user);
