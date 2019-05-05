<?php
error_reporting(E_ALL);
ini_set( 'display_errors','1');

// include the autoloader so we don't have to include the class-files by hand
include '../../autoloader.php';

// vars $pdoConnection & $adapter are available
include '../connection.php';

use Tweakers\NestedSet\Node as Comment;

$articleId = (isset($_GET['articleId']) && (int)$_GET['articleId'] > 0) ? (int)$_GET['articleId'] : 1;
$parentCommentId = (isset($_GET['parent_comment_id']) && (int)$_GET['parent_comment_id'] > 0) ? (int)$_GET['parent_comment_id'] : null;
$userId = rand(1, 4); //randomly use a user id
$description = (string)$_GET['description'];

if (is_null($parentCommentId)) {
    //this indicates that a new thread is started

    //create new comment-tree + the first comment
    $tree = $adapter->createTree(sha1(microtime()));

    //get the first comment
    $rootNode = $adapter->getNode($tree->root_node_id);

    $adapter->setDescription($tree->root_node_id, $description);
    $adapter->setArticleId($tree->root_node_id, $articleId);
    $adapter->setUserId($tree->root_node_id, $userId);

    echo 'Nieuw comment geplaatst onder artikel';
    die;
}

$commentData = [
    'article_id' => $articleId,
    'user_id' => $userId,
    'description' => $description,
];
$comment = new Comment($commentData);
if ($adapter->addChild($parentCommentId, $comment)) {
    echo 'Nieuw commentaar geplaatst!';
} else {
    echo 'Commentaar toevoegen is niet gelukt.';
}

