<?php

namespace Tweakers\Model;

use Tweakers\NestedSet\Adapter\AdapterInterface;
use PDO;
use DateTime;


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
    public $title;

    /** @var string */
    public $description;

    /** @var DateTime */
    public $dateCreated;

    /** @var CommentCollection */
    public $comments;

    public function __construct(int $id, PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->id = $id;
        if (!$this->fetchArticle()) {
            throw new \Exception("Article cannot be found.");
        }
    }

    protected function fetchArticle(): bool
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id=:id");
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
