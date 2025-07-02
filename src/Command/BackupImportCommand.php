<?php
namespace App\Command;

use App\Service\BackupLogImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'backup:import',
    description: 'Importe les rapports de sauvegarde Iperius depuis un dossier.'
)]
class BackupImportCommand extends Command
{
    private $importer;

    public function __construct(BackupLogImporter $importer)
    {
        parent::__construct();
        $this->importer = $importer;
    }

    protected function configure(): void
    {
        $this->addArgument('directory', InputArgument::REQUIRED, 'Dossier contenant les logs Iperius');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $directory = $input->getArgument('directory');
        if (!is_dir($directory)) {
            $output->writeln('<error>Dossier non trouvé : ' . $directory . '</error>');
            return Command::FAILURE;
        }
        $count = $this->importer->importFromDirectory($directory);
        $output->writeln("<info>$count logs importés depuis $directory</info>");
        return Command::SUCCESS;
    }
} 