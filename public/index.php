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
echo '<pre>';

print_r($trees);
echo '</pre>';
?>

<html>
<head>
    <style>
        .comments li {
            list-style: none;
        }

        .comments li.comment {
            list-style: disc;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <article>
            <header class="title"><?php echo htmlspecialchars($article->title);?></header>
            <section class="description"><?php echo htmlspecialchars($article->description);?></section>
            <section>
                <header class="sort"></header>
                <main>
                    <?php
                        foreach($trees as $key => $tree) {
                            echo $key;
                            echo $adapter->makeTree($tree);
                        }
                    ?>
                </main>
            </section>
        </article>
    </div>
</body>
</html>
