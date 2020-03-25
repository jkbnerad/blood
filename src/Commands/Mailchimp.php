<?php
declare(strict_types = 1);

namespace app\Commands;

use app\Database\Connection;
use app\Mailchimp\SendEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Mailchimp extends Command
{
    protected static $defaultName = 'app:mailchimp';

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mailchimp = new SendEmail(new Connection());
        $mailchimp->send();
        return 0;
    }
}
