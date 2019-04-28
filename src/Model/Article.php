<?php

namespace Tweakers\Model;

use PDO;
use DateTime;


class Article extends AbstractModel
{
    /** @var string */
    protected $table = 'articles';

    /** @var bool */
    protected $isFetched = false;

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
        parent::__construct($id, $pdo);

        if (!$this->fetchArticle()) {
            throw new \Exception("Article cannot be found.");
        }

        $this->isFetched = true;
    }

    public function getComments()
    {
        if (! $this->isFetched) { return; }

        $this->comments = new CommentCollection($this->id);
    }

    protected function fetchArticle(): bool
    {
        $result = $this->fetch();

        if (!$result) { return false; }

        $this->title = $result['title'];
        $this->description = $result['description'];
        $this->dateCreated = new DateTime($result['date_created']);

        return true;
    }
}
