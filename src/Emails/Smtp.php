<?php
declare(strict_types = 1);

namespace app\Emails;


use Nette\Mail\Mailer;
use Nette\Mail\SmtpMailer;

class Smtp
{
    public function getMailer(string $username, string $password): Mailer
    {
        $mailer = new SmtpMailer([
            'host' => 'smtpx.stable.cz', //  pokud není nastaven, použijí se hodnoty z php.ini
            'username' => $username,
            'password' => $password,
            'secure' => 'ssl',
        ]);

        return $mailer;
    }
}
