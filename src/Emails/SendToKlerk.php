<?php
declare(strict_types = 1);

namespace app\Emails;


use app\Database\Connection;
use app\Klerk\HttpClient;
use app\Klerk\Klerk;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\Debugger;

class SendToKlerk
{

    /**
     * @var \Dibi\Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection->getConnection();
    }

    public function send(OutputInterface $output, Confirm $confirm): void
    {
        $config = parse_ini_file(__DIR__ . '/../../configs/klerk.ini');

        if (!$config || empty($config['apiKey'])) {
            throw new \RuntimeException('Missing apiKey.');
        }

        $klerk = new Klerk(new HttpClient($config['apiKey']));
        while ($emails = $this->getEmailsToSend(10)) {
            foreach ($emails as $email) {
                $emailAddr = $email['email'];
                $output->writeln('Sending: ' . $emailAddr);
                $tags = explode(',', $email['tags'] ?: '');
                $save = $klerk->contact()->create($emailAddr, '', '', $tags ?: []);
                if ($save) {
                    $output->writeln('Sent');
                } else {
                    $output->writeln('Error: ' . $emailAddr);
                    Debugger::log('Email was not sent: ' . $emailAddr, Debugger::CRITICAL);
                }
                $this->setAsSent($emailAddr);
                $confirm->sendConfirmEmail($emailAddr);
                usleep((int) (1e6 * 0.2));
            }
        }
    }

    public function sendSingle(Confirm $confirm, string $email, ?string $tags = null): void
    {
        $config = parse_ini_file(__DIR__ . '/../../configs/klerk.ini');
        if (!$config || empty($config['apiKey'])) {
            throw new \RuntimeException('Missing apiKey.');
        }
        $klerk = new Klerk(new HttpClient($config['apiKey']));

        $emailAddr = $email;
        $tagsArr = explode(',', $tags ?: '');
        $save = $klerk->contact()->create($emailAddr, '', '', $tagsArr ?: []);
        if (!$save) {
            Debugger::log('Email was not save to kler: ' . $emailAddr, Debugger::CRITICAL);
        }
        $this->setAsSent($emailAddr);
        $confirm->sendConfirmEmail($emailAddr);
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
