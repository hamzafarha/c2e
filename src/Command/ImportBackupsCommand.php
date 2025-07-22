<?php
// src/Command/ImportBackupsCommand.php
namespace App\Command;

use App\Entity\BackupLog;
use App\Service\IperiusParserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class ImportBackupsCommand extends Command
{
    protected static $defaultName = 'app:import-backups';

    public function __construct(
        private EntityManagerInterface $em,
        private IperiusParserService $parser
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Import backup reports from directory')
            ->addOption('dir', null, InputOption::VALUE_REQUIRED, 'Source directory', 'var/backups/');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $directory = $input->getOption('dir');

        $finder = new Finder();
        $finder->files()->in($directory)->name(['*.eml', '*.txt']);

        if (!$finder->hasResults()) {
            $io->error("No backup files found in $directory");
            return Command::FAILURE;
        }

        foreach ($finder as $file) {
            try {
                $content = $file->getContents();
                $data = $this->parser->parseReportContent($content);
                
                $backupLog = $this->createBackupLogEntity($data);
                $this->em->persist($backupLog);
                
                $io->text(sprintf(
                    'Processed %s: %s - %s files',
                    $file->getFilename(),
                    $data['status'] ?? 'unknown',
                    $data['files_processed'] ?? 0
                ));
            } catch (\Exception $e) {
                $io->error(sprintf(
                    'Error processing %s: %s',
                    $file->getFilename(),
                    $e->getMessage()
                ));
            }
        }

        $this->em->flush();
        $io->success('Import completed');

        return Command::SUCCESS;
    }

    private function createBackupLogEntity(array $data): BackupLog
    {
        $log = new BackupLog();
        $log->setBackupName($data['backup_name'] ?? 'Unknown Backup');
        $log->setStartTime($data['start_time'] ?? null);
        $log->setEndTime($data['end_time'] ?? null);
        $log->setStatus($data['status'] ?? 'unknown');
        $log->setDetails($data['details'] ?? null);
        $log->setTotalSizeGB($data['total_size_gb'] ?? null);
        $log->setFilesProcessed($data['files_processed'] ?? null);
        $log->setErrorsCount($data['errors_count'] ?? null);
        $log->setBackupType($data['backup_type'] ?? 'automatic');
        $log->setSourcePath($data['source_path'] ?? null);
        $log->setDestinationPath($data['destination_path'] ?? null);

        return $log;
    }

}