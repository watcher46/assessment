<?php

namespace Tweakers\Model;

use PDO;
use DateTime;
use Tweakers\Model\CommentCollection;


class Article
{
    /** @var string */
    protected $table = 'articles';

    /** @var PDO */
    protected $pdo;

    /** @var bool */
    protected $isFetched = false;

    /** @var int */
    protected $id;

    /** @var string */
    protected $title;

    /** @var string */
    protected $description;

    /** @var DateTime */
    protected $dateCreated;

    /** @var CommentCollection */
    protected $comments;

    public function __construct(int $id, PDO $pdo)
    {
        $this->id = $id;
        $this->pdo = $pdo;

        if (!$this->fetchArticle($id)) {
            throw new \Exception("Article cannot be found.");
        }

        $this->isFetched = true;
    }

    public function getComments()
    {
        if (! $this->isFetched) { return; }

        $this->comments = new CommentCollection($this->id);
    }

    protected function fetchArticle(int $id): bool
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id=:id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        if (! $result) {
            return false;
        }

        $this->title = $result['title'];
        $this->description = $result['description'];
        $this->dateCreated = new DateTime($result['date_created']);

        return true;
    }
}
