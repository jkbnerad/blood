<?php
declare(strict_types = 1);

namespace app\Emails;

use app\Database\Connection;
use app\Klerk\HttpClient;
use app\Klerk\Klerk;
use Latte\Engine;
use Nette\Mail\Message;
use Nette\Mail\SmtpException;
use Tracy\Debugger;
use function Sentry\captureException;

class Confirm
{
    /**
     * @var \Dibi\Connection
     */
    private $connection;

    public function __construct(?Connection $connection)
    {
        if ($connection) {
            $this->connection = $connection->getConnection();
        }
    }

    public function confirm(string $email, string $hash): bool
    {
        $config = parse_ini_file(__DIR__ . '/../../configs/klerk.ini');

        if (!$config || empty($config['confirmSalt'])) {
            throw new \RuntimeException('Missing confirmSalt.');
        }

        if ($email && $this->getEmailHash($email, $config['confirmSalt']) === $hash) {
            $aff = $this->connection->update('Email', ['confirm' => date('Y-m-d H:i:s')])->where(['email' => $email])->execute();

            if ($aff) {
                $klerk = new Klerk(new HttpClient($config['apiKey']));
                $klerk->contact()->updateNotice($email, 'confirmed');
                return true;
            }
        }

        return false;
    }

    public function unsubscribe(string $email, string $hash): bool
    {
        $config = parse_ini_file(__DIR__ . '/../../configs/klerk.ini');

        if (!$config || empty($config['confirmSalt'])) {
            throw new \RuntimeException('Missing confirmSalt.');
        }

        if ($email && $this->getEmailHash($email, $config['confirmSalt']) === $hash) {
            $aff = $this->connection->delete('Email')->where(['email' => $email])->execute();

            if ($aff) {
                $klerk = new Klerk(new HttpClient($config['apiKey']));
                $klerk->contact()->delete($email);
            }

            return true;
        }

        return false;
    }

    public function getEmailHash(string $email, string $confirmHash): string
    {
        return sha1($email . $confirmHash);
    }

    public function sendConfirmEmail(string $email): void
    {
        $config = parse_ini_file(__DIR__ . '/../../configs/klerk.ini');

        if (!$config || empty($config['confirmSalt'])) {
            throw new \RuntimeException('Missing confirmSalt.');
        }
        $this->sendMessage($email);
        $this->connection->update('Email', ['sentWelcomeEmail' => date('Y-m-d H:i:s')])->where(['email' => $email])->execute();
    }

    private function sendMessage(string $email): void
    {
        $config = parse_ini_file(__DIR__ . '/../../configs/klerk.ini');
        if (!$config || empty($config['username']) || empty($config['password'])) {
            throw new \RuntimeException('Missing SMTP auth.');
        }

        $smtp = new Smtp();
        $mailer = $smtp->getMailer($config['username'], $config['password']);

        $message = new Message();
        $message->addTo($email);
        $message->setFrom('info@damekrev.cz', 'Damekrev.cz');
        $latte = new Engine();
        $mailParam = ['email' => $email, 'hash' => $this->getEmailHash($email, $config['confirmSalt'])];
        $message->setHtmlBody($latte->renderToString(__DIR__ . '/Templates/confirm.latte', $mailParam));

        try {
            $mailer->send($message);
        } catch (SmtpException $e) {
            captureException($e);
        }
    }

    public function sendTestEmail(): void
    {
        $this->sendMessage('jakubnerad@gmail.com');
    }

}
