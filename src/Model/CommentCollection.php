<?php

namespace Tweakers\Model;

use Tweakers\Model\Comment;

class CommentCollection
{
    /** @var int */
    protected $articleId;

    /** @var Comment[] */
    protected $comments;

    public function __construct(int $articleId)
    {
        $this->articleId = $articleId;
    }
}
