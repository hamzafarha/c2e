<?php
// src/Controller/BackupApiController.php
namespace App\Controller;

use App\Service\IperiusParserService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\BackupLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BackupApiController extends AbstractController
{
    #[Route('/api/backup/process', name: 'api_backup_process', methods: ['GET'])]
    public function processBackup(
        Request $request,
        IperiusParserService $parser,
        EntityManagerInterface $em
    ): JsonResponse {
        $content = $request->getContent();
        
        try {
            $data = $parser->parseReportContent($content);
            $backupLog = $this->createBackupLogEntity($data);
            
            $em->persist($backupLog);
            $em->flush();

            return $this->json([
                'status' => 'success',
                'id' => $backupLog->getId()
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
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
