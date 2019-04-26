<?php

namespace Tweakers\Database;

class Connection
{
    const DRIVER = "mysql";

    /** @var string */
    protected $host;
    protected $username;
    protected $password;
    protected $charset = 'utf8';
    protected $db;

    public function __construct(string $host)
    {
        if (!$host) {
            throw new \Exception('Hostname must be given to connect to the database');
        }

        $this->host = $host;
    }

    public function connect(): \PDO
    {
        if(!$this->host || !$this->username || !$this->password || !$this->db) {
            throw new \Exception('Not all credentials where given.');
        }

        return new \PDO($this->generateDsn(), $this->username, $this->password);
    }

    public function setUsername(string $username): Connection
    {
        $this->username = $username;
        return $this;
    }

    public function setPassword(string $password): Connection
    {
        $this->password = $password;
        return $this;
    }

    public function setDb(string $db): Connection
    {
        $this->db = $db;
        return $this;
    }

    public function setCharset(string $charset): Connection
    {
        $this->charset = $charset;
        return $this;
    }

    protected function generateDsn(): string
    {
        return self::DRIVER . ":host=" . $this->host . ";"
            . "dbname=" . $this->db . ";"
            . "charset=" . $this->charset . ";";


    }
}
