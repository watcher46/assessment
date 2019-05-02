<?php
/**
 * Created by PhpStorm.
 * User: Johan
 * Date: 2015-05-28
 * Time: 21:47
 */

namespace Tweakers\NestedSet\Adapter;

use Tweakers\NestedSet\Node;
use Tweakers\NestedSet\Tree;

interface AdapterInterface
{

    /**
     * @param int $nodeId
     * @return Node
     */
    public function getNode(int $nodeId): Node;

    /**
     * @param int $nodeId
     * @return bool
     */
    public function deleteNode(int $nodeId): bool;

    /**
     * @param int $nodeId
     * @param string $data
     * @return bool
     */
    public function setData(int $nodeId, string $data): bool;

    /**
     * @param int $parentId
     * @param Node $child
     * @return bool
     */
    public function addChild(int $parentId, Node $child): bool;

    /**
     * @param int $nodeId
     * @return Node[]
     */
    public function getChildren(int $nodeId): array;

    /**
     * @param int $nodeId
     * @return Node[]
     */
    public function getAllChildren(int $nodeId): array;

    /**
     * @param string $name
     * @return Tree
     */
    public function createTree(string $name): Tree;

    /**
     * @param int $id
     * @return Tree
     */
    public function getTree(int $id): Tree;

    /**
     * @param int $nodeId
     * @param int $targetParent
     * @return bool
     */
    public function moveNode(int $nodeId, int $targetParent): bool;

}
