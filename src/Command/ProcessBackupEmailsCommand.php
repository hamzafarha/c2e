<?php
// src/Command/ProcessBackupEmailsCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\IperiusMailProcessor;

class ProcessBackupEmailsCommand extends Command
{
    protected static $defaultName = 'app:process-backup-emails';
    private $mailProcessor;

    public function __construct(IperiusMailProcessor $mailProcessor)
    {
        $this->mailProcessor = $mailProcessor;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = $this->mailProcessor->processNewReports();
        $output->writeln("Processed $count new backup reports");
        return Command::SUCCESS;
    }
}