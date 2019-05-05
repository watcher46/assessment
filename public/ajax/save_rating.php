<?php
error_reporting(E_ALL);
ini_set( 'display_errors','1');

// include the autoloader so we don't have to include the class-files by hand
include '../../autoloader.php';

// vars $pdoConnection & $adapter are available
include '../../connection.php';

use Tweakers\Model\CommentThread;

//set header for json responses
header('Content-Type: application/json;charset=utf-8');

if (!isset($_POST['rating']) || !isset($_POST['commentId'])) {
    echo json_encode(['error' => 'Comment-id of rating niet opgegeven.']);
    exit;
}

$rating = $_POST['rating'];
$commentId = $_POST['commentId'];

$commentThread = new CommentThread($adapter, $pdoConnection);

echo json_encode(['result' => ['new_average' => $commentThread->setScore($rating, $commentId)]]);
