<?php
declare(strict_types=1);

namespace app\Commands;

use app\Config;
use app\HttpClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GoogleSpreadsheet extends Command
{
    protected static $defaultName = 'app:sheet';

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Save data from G Spreadsheet to JSON');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sheet = new \app\Data\GoogleSpreadsheet(new Config(), new HttpClient());

        try {
            $sheet->saveCsvToJson();
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }

        return 0;
    }
}
