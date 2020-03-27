<?php
declare(strict_types = 1);

namespace app\Mailchimp;

use app\Database\Connection;
use DrewM\MailChimp\MailChimp;

class SendEmail
{

    /**
     * @var \Dibi\Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection->getConnection();
    }

    public function send(): void
    {
        $config = parse_ini_file(__DIR__ . '/../../configs/database.ini');

        if (!$config || empty($config['apiKey']) || empty($config['audienceId'])) {
            throw new \RuntimeException('Missing apiKey or audienceId.');
        }

        $m = new MailChimp($config['apiKey']);
        $status = 'subscribed';

        while ($emails = $this->getEmailsToSend(10)) {
            foreach ($emails as $email) {
                $emailAddr = $email['email'];
                $tags = explode(',', $email['tags'] ?: '');
                $params = [
                    'email_address' => $emailAddr,
                    'status' => 'subscribed'
                ];

                if ($tags) {
                    $params['tags'] = $tags;
                }

                $response = $m->post('lists/' . $config['audienceId'] . '/members', $params);
                if ($response && isset($response['status']) && $response['status'] === $status) {
                    $this->setAsSent($emailAddr);
                }
            }
        }
    }

    private function getEmailsToSend(int $limit = 100): array
    {
        $sql = 'SELECT `email`, `tags` FROM `Email` WHERE `sentToExternalService` IS NULL ORDER BY `emailId` ASC LIMIT ?';
        return $this->connection->query($sql, $limit)->fetchAll();
    }

    private function setAsSent(string $email): void
    {
        $this->connection->query('UPDATE `Email` SET `sentToExternalService` = NOW() WHERE `email` = ?', $email);
    }
}
