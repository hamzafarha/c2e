<?php
namespace App\Controller;

use App\Repository\BackupLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackupLogController extends AbstractController
{
    
    #[Route('/historique/sauvegardes', name: 'backup_log_index', methods: ['GET'])]
    public function index(BackupLogRepository $backupLogRepository): Response
    {
        $logs = $backupLogRepository->findBy([], ['startTime' => 'DESC']);
        return $this->render('backup_log/index.html.twig', [
            'logs' => $logs,
        ]);
    }


    
}