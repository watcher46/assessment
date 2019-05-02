<?php
/**
 * Created by PhpStorm.
 * User: johan
 * Date: 15-05-29
 * Time: 01:04
 */

namespace Tweakers\NestedSet;


class Tree extends Model
{

    /**
     * @var int
     */
    public $tree_id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $root_node_id;

}
