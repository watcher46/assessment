<?php

namespace Tweakers\Model;

use PDO;

class AbstractModel
{
    protected $id;

    protected $pdo;

    protected $table;

    public function __construct(int $id, PDO $pdo)
    {
        $this->id = $id;
        $this->pdo = $pdo;
    }

    /**
     * Fetches object from database based on identitfier
     * @return array
     */
    public function fetch(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id=:id");
        $stmt->execute(['id' => $this->id]);
        $result = $stmt->fetch();
        if (! $result) {
            return false;
        }

        return $result;
    }
}
