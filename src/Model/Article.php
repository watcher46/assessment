<?php

namespace Tweakers\Model;

use Tweakers\Model\CommentThread;
use PDO;
use DateTime;
use Tweakers\NestedSet\Adapter\PDOAdapter;


class Article
{
    const ARTICLE_TABLE = "articles";

    /** @var string */
    protected $table = 'articles';

    /** @var PDO */
    protected $pdo;

    /** @var PDOAdapter */
    protected $adapter;

    /** @var bool */
    protected $isFetched = false;

    /** @var int */
    protected $id;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var DateTime */
    public $dateCreated;

    /** @var array */
    public $comments;

    public function __construct(int $id, PDO $pdo, PDOAdapter $adapter)
    {
        $this->pdo = $pdo;
        $this->adapter = $adapter;
        $this->id = $id;
        if (!$this->fetchArticle()) {
            throw new \Exception("Article cannot be found.");
        }
    }

    public function getComments()
    {
        if (! $this->comments) {
            $commentTree = new CommentThread($this->adapter, $this->pdo);
            $this->comments = $commentTree->getCommentThreads($this->id);
        }

        return $this->comments;
    }

    protected function fetchArticle(): bool
    {
        $sql = "
            SELECT * 
            FROM " . self::ARTICLE_TABLE . " 
            WHERE id=:id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $this->id]);
        $result = $stmt->fetch();

        if (! $result) {
            return false;
        }

        $this->title = $result['title'];
        $this->description = $result['description'];
        $dateCreated = new DateTime($result['date_created']);
        $this->dateCreated = $dateCreated->format('d-m-Y H:i:s');

        return true;
    }
}
