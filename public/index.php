<?php
error_reporting(E_ALL);
ini_set( 'display_errors','1');

// include the autoloader so we don't have to include the class-files by hand
include '../autoloader.php';

// vars $pdoConnection & $adapter are available
include 'connection.php';

use Tweakers\Model\Article;

$articleId = (int)$_GET['articleId'] ?: 1;

$article = new Article($articleId, $pdoConnection);
$trees = $adapter->getAllTreesFromArticle($articleId);
?>

<html>
<head>
    <title><?php echo htmlspecialchars($article->title);?> - Tweakers</title>
    <link rel="stylesheet" type="text/css" href="styles.css" />
</head>
<body>
    <div class="wrapper">
        <article class="content">
            <header class="title"><h1><?php echo htmlspecialchars($article->title);?></h1></header>
            <section class="description"><?php echo htmlspecialchars($article->description);?></section>
            <section>
                <header class="sort"></header>
                <main>
                    <h2>Reacties:</h2>
                    <?php foreach($trees as $key => $tree): ?>
                        <ul class="comments">
                        <?php
                            $currDepth = 0;

                            /** @var \Tweakers\NestedSet\Node $node */
                            foreach( $tree as $node ) {
                                if ($node->depth > $currDepth) {
                                    echo '<ul>';
                                } elseif($node->depth > 0) {
                                    echo '</li>';
                                }

                                if ($node->depth < $currDepth) {
                                    echo str_repeat("</ul>", $currDepth - $node->depth); // close sub tree if level down
                                }

                                $commentCreated = new \DateTime($node->date_created);
                                $comment = "
                                    <div class=\"comment-body\">
                                        <header>
                                            <span class='username'>{$node->user_name}</span>
                                            <span class='date'>geplaatst op: {$commentCreated->format('d-m-Y H:i')}</span>
                                            <span class='rating'>
                                                <button class='rate min-one'>-1</button>
                                                <button class='rate zero'>0</button>
                                                <button class='rate plus-one'>+1</button>
                                                <button class='rate plus-two'>+2</button>
                                                <button class='rate plus-three'>+3</button>
                                                <span class='average'>Score:</span>
                                            </span>
                                            <span class='id'>id: {$node->id}</span>
                                        </header>
                                        <section>{$node->description}</section>
                                    </div>
                                ";

                                echo "<li class=\"comment\">{$comment}";
                                $currDepth = $node->depth;
                            }

                            if ($currDepth > 0) {
                                while($currDepth >= 0) {
                                    echo "</li></ul>";
                                    $currDepth--;
                                }
                            }
                        ?>
                   <?php endforeach; ?>
                </main>
            </section>
        </article>
    </div>
</body>
</html>
