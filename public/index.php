<?php
error_reporting(E_ALL);
ini_set( 'display_errors','1');

include '../autoloader.php';

use Tweakers\Database\Connection;

$host = 'db'; //only works when running in docker
$username = 'tweakers-test';
$password = 'test-tweakers';
$database = 'tweakers';

$connection = new Connection('db');
$connection = $connection->setUsername($username)->setPassword($password)->setDb($database)->connect();

$query = $connection->query("SHOW TABLES")->execute();
var_dump($query);
