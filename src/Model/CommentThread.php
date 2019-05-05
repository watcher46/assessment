<?php

namespace Tweakers\Model;

use Tweakers\NestedSet\Adapter\PDOAdapter;
use Tweakers\NestedSet\Node;
use PDO;

class CommentThread
{
    const COMMENT_TABLE = "comments";
    const COMMENT_SCORE_TABLE = "comments_score";

    protected $adapter;
    protected $pdo;

    /**
     * CommentThread constructor.
     *
     * @param PDOAdapter $adapter
     * @param PDO $pdo
     */
    public function __construct(PDOAdapter $adapter, PDO $pdo)
    {
        $this->adapter = $adapter;
        $this->pdo = $pdo;
    }

    /**
     * Get all comment threads based on the article-id
     *
     * @param int $articleId
     * @param string $sortOrder
     * @return array
     */
    public function getCommentThreads(int $articleId, string $sortOrder): array
    {
        if ($sortOrder !== 'ASC' && $sortOrder !== 'DESC') {
            $sortOrder = 'ASC';
        }

        $sql = "
            SELECT c.id
            FROM " . self::COMMENT_TABLE . " AS c
            LEFT JOIN comments_tree AS ct ON c.tree_id = ct.tree_id
            WHERE c.article_id = :article_id
            AND c.depth = :depth
            ORDER BY date_created {$sortOrder}
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':depth' => Node::INITIAL_DEPTH,
            ':article_id' => $articleId
        ]);

        $rootCommentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($rootCommentIds)) {
            throw new \RuntimeException("Trees with article id {$articleId} not found");
        }

        $trees = [];
        foreach($rootCommentIds as $id) {
            $trees[] = $this->adapter->getAllChildren($id);
        }

        return $trees;
    }

    /**
     * Sets score on a comment, returning the average score of the comment
     *
     * @param int $rating
     * @param int $commentId
     * @return float
     */
    public function setScore(int $rating, int $commentId): float
    {
        $sql = "
            INSERT INTO " . self::COMMENT_SCORE_TABLE . "
            SET comment_id = :comment_id, score = :score
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':comment_id' => $commentId, ':score' => $rating]);

        //after adding the new score, return the new average of the comment
        $sql = "
            SELECT AVG(score) as score
            FROM " . self::COMMENT_SCORE_TABLE . "
            WHERE comment_id = :comment_id
            GROUP BY comment_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':comment_id' => $commentId]);
        $row = $stmt->fetchColumn();

        return round($row);
    }
}
