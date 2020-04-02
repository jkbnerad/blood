<?php
declare(strict_types = 1);

namespace app\Commands;

use app\Azure\EventGrid\Client;
use app\Azure\EventGrid\Event;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EventGrid extends Command
{
    protected static $defaultName = 'app:eventgrid';

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('');
        $this->addOption('email', 'e', InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventGrid = new Event(new Client());
        $eventGrid->send($input->getOption('email'));
        return 0;
    }
}
