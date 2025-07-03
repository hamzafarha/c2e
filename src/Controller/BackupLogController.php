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
    #[Route('/historique/sauvegardes/importer', name: 'backup_log_import', methods: ['GET', 'POST'])]
    public function import(Request $request, BackupLogImporter $importer): Response
    {
        $form = $this->createFormBuilder()
            ->add('emlFile', FileType::class, [
                'label' => 'Fichier .eml',
                'required' => true,
                'constraints' => [
                    new File([
                        'mimeTypes' => ['message/rfc822', 'text/plain'],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier .eml valide',
                    ])
                ]
            ])
            ->add('importer', SubmitType::class, ['label' => 'Importer'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emlFile = $form->get('emlFile')->getData();
            $tempPath = $emlFile->getRealPath();

            try {
                $log = $importer->importFromEml($tempPath);
                $this->addFlash('success', 'Le fichier a été importé avec succès!');
                return $this->redirectToRoute('backup_log_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'import: ' . $e->getMessage());
            }
        }

        return $this->render('backup_log/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
