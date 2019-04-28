<?php

namespace Tweakers\Model;

use PDO;

class User extends AbstractModel
{

    protected $table = 'users';

    protected $isFetched = false;

    public $name;

    public function __construct(int $id, PDO $pdo)
    {
        parent::__construct($id, $pdo);

        if (!$this->fetchUser()) {
            throw new \Exception("User cannot be found.");
        }

        $this->isFetched = true;
    }

    public function fetchUser()
    {
        $user = $this->fetch();

        if (!$user) { return false; }

        $this->name = $user['name'];
        return true;
    }
}
