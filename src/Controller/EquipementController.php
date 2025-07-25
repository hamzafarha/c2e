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
        if ($this->isCsrfTokenValid('delete'.$equipement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($equipement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_equipement_index', [], Response::HTTP_SEE_OTHER);
    }
}
