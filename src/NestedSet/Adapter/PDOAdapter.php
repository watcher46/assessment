<?php
/**
 * Created by PhpStorm.
 * User: Johan
 * Date: 2015-05-28
 * Time: 22:36
 */

namespace Tweakers\NestedSet\Adapter;

use Tweakers\NestedSet\Node;
use Tweakers\NestedSet\Tree;
use PDO;

class PDOAdapter implements AdapterInterface
{

    /**
     * @var PDO
     */
    private $db;

    /**
     * @var string
     */
    private $tablePrefix;

    /**
     * @param PDO $db
     * @param string $tablePrefix
     */
    function __construct(PDO $db, string $tablePrefix = 'nested_set_')
    {
        $this->db = $db;
        $this->tablePrefix = $tablePrefix;
    }

    /**
     * Write a node to the database.
     * @param Node $node
     * @return bool
     */
    private function insertNode(Node $node): bool
    {
        $sql = <<<EOD
insert into {$this->tablePrefix}node (tree_id, lft, rgt, data, depth)
values (:tree_id, :left, :right, :data, :depth)
EOD;
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([
            ':tree_id' => $node->tree_id,
            ':left' => $node->lft,
            ':right' => $node->rgt,
            ':data' => $node->data,
            ':depth' => $node->depth
        ]);
        $node->node_id = $this->db->lastInsertId();
        return $res;
    }

    /**
     * Grow or shrink the tree from the specified position. If $value is positive, the tree grows from $position
     * to the right. If $value is negative, the tree shrinks from $position to the left.
     * @param int $position
     * @param int $treeId
     * @param int $value
     * @return bool
     */
    private function resizeAt(int $position, int $treeId, int $value): bool
    {
        $sql = <<<EOD
update {$this->tablePrefix}node
set
    lft = (select case when lft > :position_1 then lft + :value_1 else lft end),
    rgt = (select case when rgt > :position_2 then rgt + :value_2 else rgt end)
where tree_id = :tree_id
EOD;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':position_1' => $position,
            ':position_2' => $position,
            ':tree_id' => $treeId,
            ':value_1' => $value,
            ':value_2' => $value,
        ]);
    }

    /**
     * @param int $id
     * @return Node
     */
    public function getNode(int $id): Node
    {
        $stmt = $this->db->prepare("select * from {$this->tablePrefix}node where node_id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($data)) {
            throw new \RuntimeException("Node with id {$id} not found");
        }
        return new Node($data);
    }

    /**
     * @param int $nodeId
     * @return bool
     */
    public function deleteNode(int $nodeId): bool
    {
        $node = $this->getNode($nodeId);
        $sql = <<<EOD
delete from {$this->tablePrefix}node
where lft >= :left
and rgt <= :right
and tree_id = :tree_id
EOD;
        $stmt = $this->db->prepare($sql);
        $r1 = $stmt->execute([
            ':left' => $node->lft,
            ':right' => $node->rgt,
            ':tree_id' => $node->tree_id,
        ]);

        // since a node was deleted we must shrink the tree to remove the gap
        $r2 = $this->resizeAt($node->rgt, $node->tree_id, -(2 + $node->getChildCount() * 2));

        return $r1 && $r2;
    }

    /**
     * @param int $nodeId
     * @param string $data
     * @return bool
     */
    public function setData(int $nodeId, string $data): bool
    {
        $stmt = $this->db->prepare("update {$this->tablePrefix}node set data = :data where node_id = :id");
        return $stmt->execute([
            ':id' => $nodeId,
            ':data' => $data,
        ]);
    }

    /**
     * @param int $parentId
     * @param Node $child
     * @return bool
     */
    public function addChild(int $parentId, Node $child): bool
    {
        $parent = $this->getNode($parentId);
        $child->lft = $parent->rgt;
        $child->rgt = $child->lft + 1;
        $child->tree_id = $parent->tree_id;
        $child->depth = $parent->depth + 1;

        $r1 = $this->resizeAt($parent->rgt - 1, $parent->tree_id, 2);
        $r2 = $this->insertNode($child);

        return $r1 && $r2;
    }

    /**
     * @param int $nodeId
     * @return Node[]
     */
    public function getChildren(int $nodeId): array
    {
        $parent = $this->getNode($nodeId);
        $sql = <<<EOD
select *
from {$this->tablePrefix}node
where lft > :left
    and rgt < :right
    and tree_id = :tree_id
    and depth = :depth
EOD;

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':left' => $parent->lft,
            ':right' => $parent->rgt,
            ':tree_id' => $parent->tree_id,
            ':depth' => $parent->depth + 1,
        ]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function($row) {
            return new Node($row);
        }, $data);
    }

    /**
     * @param int $nodeId
     * @return Node[]
     */
    public function getAllChildren(int $nodeId): array
    {
        $parent = $this->getNode($nodeId);
        $sql = <<<EOD
select *
from {$this->tablePrefix}node
where lft > :left
    and rgt < :right
    and tree_id = :tree_id
EOD;

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':left' => $parent->lft,
            ':right' => $parent->rgt,
            ':tree_id' => $parent->tree_id,
        ]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function($row) {
            return new Node($row);
        }, $data);
    }

    /**
     * @param string $name
     * @return Tree
     */
    public function createTree(string $name): Tree
    {
        $stmt = $this->db->prepare("insert into {$this->tablePrefix}tree (name) values (:name)");
        $stmt->execute([':name' => $name]);
        $last = $this->db->lastInsertId();
        $rootNode = new Node([
            'lft' => Node::INITIAL_LEFT,
            'rgt' => Node::INITIAL_RIGHT,
            'depth' => Node::INITIAL_DEPTH,
            'tree_id' => $last
        ]);
        $this->insertNode($rootNode);
        return $this->getTree($last);
    }

    /**
     * @param int $id
     * @return Tree
     */
    public function getTree(int $id): Tree
    {
        $stmt = $this->db->prepare("select * from {$this->tablePrefix}tree where tree_id = :id");
        $stmt->execute([
            ':id' => $id
        ]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($data)) {
            throw new \RuntimeException("Tree with id {$id} not found");
        }
        $tree = new Tree($data);

        // find the root node
        $sql = <<<EOD
select node_id
from {$this->tablePrefix}node
where depth = :depth
and tree_id = :tree_id
EOD;
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':depth' => Node::INITIAL_DEPTH,
            ':tree_id' => $id,
        ]);
        $rootId = $stmt->fetchColumn();
        $tree->root_node_id = $rootId;
        return $tree;
    }

    /**
     * @param int $nodeId
     * @param int $targetParent
     * @return bool
     */
    public function moveNode(int $nodeId, int $targetParent): bool
    {
        $node = $this->getNode($nodeId);
        $target = $this->getNode($targetParent);

        // expand the target parent to fit the node and its children
        $growth = 2 + $node->getChildCount() * 2;
        $r1 = $this->resizeAt($target->rgt - 1, $target->tree_id, $growth);

        $moveDelta = $target->rgt + $growth - $node->rgt - 1;
        $depthDelta = $target->depth + 1 - $node->depth;

        // use the deltas to move the node and its children to the parent
        $sql = <<<EOD
update {$this->tablePrefix}node
set
    lft = lft + :move_delta_1,
    rgt = rgt + :move_delta_2,
    depth = depth + :depth_delta
where lft >= :left
and rgt <= :right
and tree_id = :tree_id
EOD;
        $stmt = $this->db->prepare($sql);
        $r2 = $stmt->execute([
            ':move_delta_1' => $moveDelta,
            ':move_delta_2' => $moveDelta,
            ':left' => $node->lft,
            ':right' => $node->rgt,
            ':tree_id' => $node->tree_id,
            ':depth_delta' => $depthDelta,
        ]);

        // remove the leftover space after moving the node
        $r3 = $this->resizeAt($node->rgt, $node->tree_id, -$growth);

        return $r1 && $r2 && $r3;
    }
}
