<?php
declare(strict_types=1);

namespace web\Models;

use Nette\Utils\DateTime;

class Emails
{
    /**
     * @var \Nette\Database\Connection
     */
    private $database;

    public function __construct(\Nette\Database\Connection $database)
    {
        $this->database = $database;
    }

    public function getCount(): int
    {
        return (int) $this->database->query('SELECT COUNT(*) FROM `Email`')->fetchField();
    }

    public function getCountInterval(string $from, string $to): int
    {
        $sql = 'SELECT COUNT(*) FROM `Email` WHERE `date` BETWEEN ? AND ?';
        return (int) $this->database->query($sql, $from, $to)->fetchField();
    }

    public function getCountByDays(int $days): array
    {
        $sql = 'SELECT `date`, COUNT(*) as `count` FROM `Email` WHERE `date` > NOW() - INTERVAL ? DAY GROUP BY `date`';
        $results = $this->database->query($sql, $days)->fetchAll();

        $final = [];
        foreach($results as $result) {
            /** @var DateTime $date */
            $date = $result['date'];
            $count = $result['count'];
            $final[$date->format('d/m')] = $count;
        }

        return $final;
    }
}
