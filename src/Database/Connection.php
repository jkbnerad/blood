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
        if (strpos($config['dsn'], '.azure.com') !== false) { // set SSL for Azure
            $config['options'][\PDO::MYSQL_ATTR_SSL_CA] = '/var/www/html/krev-dev/configs/BaltimoreCyberTrustRoot.crt.pem';
        }
        $this->connection = new \Dibi\Connection($config);
    }

    public function getConnection(): \Dibi\Connection
    {
        return $this->connection;
    }
}
