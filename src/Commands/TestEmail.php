<?php
declare(strict_types = 1);

namespace app\Commands;

use app\Database\Connection;
use app\Emails\Confirm;
use app\Emails\SendToKlerk;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestEmail extends Command
{
    protected static $defaultName = 'app:test-email';

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $confirmEmail = new Confirm(null);
        $confirmEmail->sendTestEmail();
        return 0;
    }
}
