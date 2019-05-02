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
                    <?php
                        foreach($trees as $key => $tree) {
                            echo $adapter->makeTree($tree);
                        }
                    ?>
                </main>
            </section>
        </article>
    </div>
</body>
</html>
