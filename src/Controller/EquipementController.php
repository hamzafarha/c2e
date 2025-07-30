<?php

namespace App\Controller;

use App\Entity\Equipement;
use App\Form\EquipementForm;
use App\Repository\EquipementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;

#[Route('/equipement')]
final class EquipementController extends AbstractController
{
    #[Route(name: 'app_equipement_index', methods: ['GET'])]
    public function index(Request $request, EquipementRepository $equipementRepository): Response
    {
        $query = $request->query->get('q', '');
        $type = $request->query->get('type', '');
        $order = $request->query->get('order', 'desc');

        $qb = $equipementRepository->createQueryBuilder('e');

        if (!empty($query)) {
            $qb->where('e.nomeq LIKE :query OR e.referenceeq LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        if (!empty($type)) {
            $qb->andWhere('e.typeeq = :type')
                ->setParameter('type', $type);
        }

        $qb->orderBy('e.ideq', $order === 'asc' ? 'ASC' : 'DESC');

        $equipements = $qb->getQuery()->getResult();

        return $this->render('equipement/index.html.twig', [
            'equipements' => $equipements,
        ]);
    }

    #[Route('/new', name: 'app_equipement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipement = new Equipement();
        $form = $this->createForm(EquipementForm::class, $equipement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($equipement);
            $entityManager->flush();

            // Génération du texte à encoder dans le QR code
            $qrText = sprintf(
                "Nom : %s\nRéférence : %s\nModèle : %s\nNuméro de série : %s\nLocalisation : %s\nÉtat : %s",
                $equipement->getNomeq(),
                $equipement->getReferenceeq(),
                $equipement->getModeleeq(),
                $equipement->getNumserieeq(),
                $equipement->getLocalisationeq(),
                $equipement->getEtat()
            );
            $qrPath = sprintf('qr/equipement_%d.png', $equipement->getId());
            $fullPath = $this->getParameter('kernel.project_dir') . '/public/' . $qrPath;

            $builder = new Builder(
                writer: new PngWriter(),
                data: $qrText
            );
            $result = $builder->build();
            $result->saveToFile($fullPath);
            $equipement->setCodeQr($qrPath);
            $entityManager->flush();

            return $this->redirectToRoute('app_equipement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipement/new.html.twig', [
            'equipement' => $equipement,
            'form' => $form,
        ]);
    }

    #[Route('/search', name: 'app_equipement_search', methods: ['GET'])]
    public function search(Request $request, EquipementRepository $equipementRepository): Response
    {
        $query = $request->query->get('q', '');
        $order = $request->query->get('order', 'desc');
        $type = $request->query->get('type', '');

        $qb = $equipementRepository->createQueryBuilder('e');

        if (!empty($query)) {
            $qb->where('e.nomeq LIKE :query OR e.referenceeq LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        if (!empty($type)) {
            $qb->andWhere('e.typeeq = :type')
                ->setParameter('type', $type);
        }

        $qb->orderBy('e.ideq', $order === 'asc' ? 'ASC' : 'DESC');

        $equipements = $qb->getQuery()->getResult();

        return $this->render('equipement/_list.html.twig', [
            'equipements' => $equipements,
        ]);
    }

    #[Route('/{ideq}', name: 'app_equipement_show', methods: ['GET'], requirements: ['ideq' => '\\d+'])]
    public function show(Equipement $equipement): Response
    {
        return $this->render('equipement/show.html.twig', [
            'equipement' => $equipement,
        ]);
    }

    #[Route('/{ideq}/edit', name: 'app_equipement_edit', methods: ['GET', 'POST'], requirements: ['ideq' => '\\d+'])]
    public function edit(Request $request, Equipement $equipement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EquipementForm::class, $equipement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_equipement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipement/edit.html.twig', [
            'equipement' => $equipement,
            'form' => $form,
        ]);
    }

    #[Route('/{ideq}', name: 'app_equipement_delete', methods: ['POST'], requirements: ['ideq' => '\\d+'])]
    public function delete(Request $request, Equipement $equipement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $equipement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($equipement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_equipement_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/import-excel', name: 'app_equipement_import_excel', methods: ['POST'])]
    public function importExcel(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$request->files->has('fichier_excel')) {
            $this->addFlash('error', 'Aucun fichier n\'a été téléchargé.');
            return $this->redirectToRoute('app_equipement_index');
        }

        $fichier = $request->files->get('fichier_excel');

        // Vérification de l'extension
        $extension = $fichier->getClientOriginalExtension();
        if (!in_array($extension, ['xlsx', 'xls'])) {
            $this->addFlash('error', 'Le fichier doit être un fichier Excel (.xlsx ou .xls)');
            return $this->redirectToRoute('app_equipement_index');
        }

        try {
            $spreadsheet = IOFactory::load($fichier->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Supprimer l'en-tête si nécessaire
            array_shift($rows);

            $importCount = 0;
            foreach ($rows as $row) {
                if (count($row) < 2) continue; // Ignorer les lignes vides

                // Détection automatique du type (PC ou Imprimante)
                $isPrinter = (count($row) >= 4 && (str_contains($row[1] ?? '', 'ZD420') || str_contains($row[1] ?? '', 'TDP43ME')));

                $equipement = new Equipement();

                if ($isPrinter) {
                    // Format Imprimante
                    $equipement->setTypeeq('Imprimantes');
                    $equipement->setNomeq($row[2] ?? 'Imprimante ' . ($importCount + 1));
                    $equipement->setNumserieeq($row[0] ?? '');
                    $equipement->setModeleeq($row[1] ?? '');
                    $equipement->setLocalisationeq($row[2] ?? '');
                    $equipement->setReferenceeq($row[3] ?? '');
                } else {
                    // Format PC
                    $equipement->setTypeeq('PC');
                    $equipement->setNomeq($row[0] ?? '');
                    $equipement->setNumserieeq($row[1] ?? '');
                    $equipement->setModeleeq($row[2] ?? '');
                    $equipement->setLocalisationeq($row[3] ?? '');
                    $equipement->setReferenceeq($row[4] ?? '');
                }

                $equipement->setEtat('en_service');
                $entityManager->persist($equipement);
                $importCount++;
            }

            $entityManager->flush();
            $this->addFlash('success', sprintf('%d équipements ont été importés avec succès.', $importCount));
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'importation: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_equipement_index');
    }
}
