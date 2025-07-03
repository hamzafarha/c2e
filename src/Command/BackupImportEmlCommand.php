<?php
namespace App\Command;

use App\Service\BackupLogImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'backup:import-eml',
    description: 'Importe un ou plusieurs rapports de sauvegarde Iperius depuis des fichiers .eml.'
)]
class BackupImportEmlCommand extends Command
{
    private $importer;

    public function __construct(BackupLogImporter $importer)
    {
        parent::__construct();
        $this->importer = $importer;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('eml', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Un ou plusieurs fichiers .eml à importer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $emlFiles = $input->getArgument('eml');
        $imported = 0;
        foreach ($emlFiles as $emlFile) {
            if (!is_file($emlFile)) {
                $output->writeln("<error>Fichier non trouvé : $emlFile</error>");
                continue;
            }
            $log = $this->importer->importFromEml($emlFile);
            if ($log) {
                $output->writeln("<info>Import réussi : $emlFile</info>");
                $imported++;
            } else {
                $output->writeln("<comment>Non reconnu ou déjà importé : $emlFile</comment>");
            }
        }
        $output->writeln("<info>$imported rapport(s) importé(s).</info>");
        return Command::SUCCESS;
    }
} 