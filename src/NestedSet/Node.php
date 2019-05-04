<?php
/**
 * Created by PhpStorm.
 * User: Johan
 * Date: 2015-05-28
 * Time: 21:39
 */

namespace Tweakers\NestedSet;


class Node extends Model
{

    const INITIAL_LEFT = 0;
    const INITIAL_RIGHT = 1;
    const INITIAL_DEPTH = 0;

    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $tree_id;

    /**
     * Left value of the node. Read only.
     * @var int
     */
    public $lft;

    /**
     * Right value of the node. Read only.
     * @var int
     */
    public $rgt;

    /**
     * @var string
     */
    public $data;

    /**
     * Depth of the node. Read only.
     * @var int
     */
    public $depth;

    /** @var int */
    public $article_id;

    /** @var int */
    public $user_id;

    /** @var string */
    public $user_name;

    /** @var string */
    public $description;

    /** @var string */
    public $date_created;

    /** @var int */
    public $average_score;

    /**
     * @return int
     */
    public function getChildCount(): int
    {
        return (int) floor(($this->rgt - $this->lft) / 2);
    }

}
