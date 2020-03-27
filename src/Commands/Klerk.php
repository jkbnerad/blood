<?php
declare(strict_types = 1);

namespace app\Commands;

use app\Database\Connection;
use app\Emails\Confirm;
use app\Emails\SendToKlerk;
use app\Emails\Smtp;
use Nette\Mail\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Klerk extends Command
{
    protected static $defaultName = 'app:klerk';

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sendEmails = new SendToKlerk(new Connection());
        $sendEmails->send($output, new Confirm(new Connection()));
        return 0;
    }
}
