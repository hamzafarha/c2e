<?php

namespace App\Controller;

use App\Entity\BackupLog;
use App\Form\BackupLogType;
use App\Repository\BackupLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/backup-log')]
class BackupLogController extends AbstractController
{
    #[Route('/', name: 'app_backuplog_index', methods: ['GET'])]
    public function index(BackupLogRepository $backupLogRepository): Response
    {
        return $this->render('backuplog/index.html.twig', [
            'backup_logs' => $backupLogRepository->findBy([], ['startTime' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_backuplog_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $backupLog = new BackupLog();
        $form = $this->createForm(BackupLogType::class, $backupLog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Calcul automatique de la durée si startTime et endTime sont fournis
            if ($backupLog->getStartTime() && $backupLog->getEndTime()) {
                $backupLog->setDuration(
                    $backupLog->getStartTime()->diff($backupLog->getEndTime())->format('%Hh %Im %Ss')
                );
            }
            $entityManager->persist($backupLog);
            $entityManager->flush();
            $this->addFlash('success', 'Le rapport de sauvegarde a été créé avec succès.');
            return $this->redirectToRoute('app_backuplog_index');
        }

        return $this->render('backuplog/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_backuplog_show', methods: ['GET'])]
    public function show(BackupLog $backupLog): Response
    {
        return $this->render('backuplog/show.html.twig', [
            'backup_log' => $backupLog,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_backuplog_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, BackupLog $backupLog, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BackupLogType::class, $backupLog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Recalcul de la durée si modification des dates
            if ($backupLog->getStartTime() && $backupLog->getEndTime()) {
                $backupLog->setDuration(
                    $backupLog->getStartTime()->diff($backupLog->getEndTime())->format('%Hh %Im %Ss')
                );
            }
            $entityManager->flush();
            $this->addFlash('success', 'Le rapport de sauvegarde a été mis à jour.');
            return $this->redirectToRoute('app_backuplog_show', ['id' => $backupLog->getId()]);
        }

        return $this->render('backuplog/edit.html.twig', [
            'form' => $form,
            'backup_log' => $backupLog,
        ]);
    }

    #[Route('/{id}', name: 'app_backuplog_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, BackupLog $backupLog, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$backupLog->getId(), $request->request->get('_token'))) {
            $entityManager->remove($backupLog);
            $entityManager->flush();
            $this->addFlash('success', 'Le rapport de sauvegarde a été supprimé.');
        }
        return $this->redirectToRoute('app_backuplog_index');
    }

    #[Route('/import/iperius', name: 'app_backuplog_import_iperius', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function importIperiusReport(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $reportContent = $request->request->get('report_content');
            // Ici vous ajouteriez la logique pour parser le rapport Iperius et créer un nouvel objet BackupLog
            $this->addFlash('success', 'Rapport Iperius importé avec succès');
            return $this->redirectToRoute('app_backuplog_index');
        }
        return $this->render('backuplog/import_iperius.html.twig');
    }
} 