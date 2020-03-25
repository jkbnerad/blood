<?php
declare(strict_types = 1);

namespace app\Database;

class Connection
{
    /**
     * @var \Dibi\Connection
     */
    private $connection;

    public function __construct()
    {
        $config = parse_ini_file(__DIR__ . '/../../configs/database.ini') ?: [];
        $this->connection = new \Dibi\Connection($config);
    }

    public function getConnection(): \Dibi\Connection
    {
        return $this->connection;
    }
}
