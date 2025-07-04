<?php

namespace App\Controller;

use App\Repository\BackupLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\BackupLogImporter;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    // BackupLogController.php
    #[Route('/historique/sauvegardes/import', name: 'backup_log_import', methods: ['POST'])]
    public function import(Request $request, BackupLogImporter $importer): RedirectResponse
    {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('eml_file');
        if ($file && $file->isValid()) {
            $tmpPath = $file->getPathname();
            $log = $importer->importFromEml($tmpPath);
            if ($log) {
                $this->addFlash('success', 'Rapport importé avec succès.');
            } else {
                $this->addFlash('danger', 'Le fichier n\'a pas pu être importé ou n\'est pas reconnu.');
            }
        } else {
            $this->addFlash('danger', 'Aucun fichier valide envoyé.');
        }
        return $this->redirectToRoute('backup_log_index');
    }
}
