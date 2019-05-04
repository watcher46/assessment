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
insert into {$this->tablePrefix} (tree_id, lft, rgt, depth, date_created, article_id, user_id, description)
values (:tree_id, :left, :right, :depth, now(), :article_id, :user_id, :description)
EOD;
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([
            ':tree_id' => $node->tree_id,
            ':left' => $node->lft,
            ':right' => $node->rgt,
            ':depth' => $node->depth,
            ':article_id' => $node->article_id,
            ':user_id' => $node->user_id,
            ':description' => $node->description,
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
update {$this->tablePrefix}
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
        $stmt = $this->db->prepare("select * from {$this->tablePrefix} where id = :id");
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
delete from {$this->tablePrefix}
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
        $stmt = $this->db->prepare("update {$this->tablePrefix} set description = :data where id = :id");
        return $stmt->execute([
            ':id' => $nodeId,
            ':data' => $data,
        ]);
    }

    /**
     * @param int $nodeId
     * @param int $articleId
     * @return bool
     */
    public function setArticleId(int $nodeId, int $articleId): bool
    {
        $stmt = $this->db->prepare("update {$this->tablePrefix} set article_id = :article_id where id = :id");
        return $stmt->execute([
            ':id' => $nodeId,
            ':article_id' => $articleId,
        ]);
    }

    /**
     * @param int $nodeId
     * @param int $userId
     * @return bool
     */
    public function setUserId(int $nodeId, int $userId): bool
    {
        $stmt = $this->db->prepare("update {$this->tablePrefix} set user_id = :user_id where id = :id");
        return $stmt->execute([
            ':id' => $nodeId,
            ':user_id' => $userId,
        ]);
    }

    /**
     * @param int $nodeId
     * @param string $description
     * @return bool
     */
    public function setDescription(int $nodeId, string $description): bool
    {
        $stmt = $this->db->prepare("update {$this->tablePrefix} set description = :description where id = :id");
        return $stmt->execute([
            ':id' => $nodeId,
            ':description' => $description,
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
from {$this->tablePrefix}
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
select c.*, u.name as user_name
from {$this->tablePrefix} c
left join users as u on c.user_id = u.id
where lft >= :left
    and rgt <= :right
    and tree_id = :tree_id
order by lft ASC
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
        $stmt = $this->db->prepare("insert into {$this->tablePrefix}_tree (name) values (:name)");
        $stmt->execute([':name' => $name]);
        $last = $this->db->lastInsertId();
        $rootNode = new Node([
            'lft' => Node::INITIAL_LEFT,
            'rgt' => Node::INITIAL_RIGHT,
            'depth' => Node::INITIAL_DEPTH,
            'tree_id' => $last,
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
        $stmt = $this->db->prepare("select * from {$this->tablePrefix}_tree where tree_id = :id");
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
select id
from {$this->tablePrefix}
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
     * Every root comment on an article is a new tree
     * Therefore to render all comments for an article, all trees related to the article must be rendered
     *
     * @param int $articleId
     * @return array
     */
    public function getAllTreesFromArticle(int $articleId)
    {
        $sql = <<<EOD
select c.id
from {$this->tablePrefix} as c
left join {$this->tablePrefix}_tree as ct ON c.tree_id = ct.tree_id
where c.article_id = :article_id
and c.depth = :depth
order by date_created asc
EOD;

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':depth' => Node::INITIAL_DEPTH,
            ':article_id' => $articleId
        ]);

        $rootCommentIds = $stmt->fetchAll();
        if (empty($rootCommentIds)) {
            throw new \RuntimeException("Trees with article id {$articleId} not found");
        }

        $trees = [];
        foreach($rootCommentIds as $row) {
            $trees[] = $this->getAllChildren($row['id']);
        }

        return $trees;
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
update {$this->tablePrefix}
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
