<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Form\InterventionForm;
use App\Repository\InterventionRepository;
use App\Repository\EquipementRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/intervention')]
final class InterventionController extends AbstractController
{
    #[Route(name: 'app_intervention_index', methods: ['GET'])]
    public function index(InterventionRepository $interventionRepository): Response
    {
        return $this->render('intervention/index.html.twig', [
            'interventions' => $interventionRepository->findAll(),
        ]);
    }

    #[Route('/new/{ideq?}', name: 'app_intervention_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, ?int $ideq = null, EquipementRepository $equipementRepository): Response
{
    $intervention = new Intervention();

    if ($ideq) {
        $equipement = $equipementRepository->find($ideq);
        if ($equipement) {
            $intervention->setEquipement($equipement);
        }
    }

    $form = $this->createForm(InterventionForm::class, $intervention);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($intervention);
        $entityManager->flush();

        return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
    }

   return $this->render('intervention/new.html.twig', [
    'intervention' => $intervention,
    'form' => $form,
    'equipement' => $equipement ?? null, // Passe l’équipement à la vue
]);

}

    #[Route('/search', name: 'app_intervention_search', methods: ['GET'])]
    public function search(Request $request, InterventionRepository $interventionRepository): Response
    {
        $query = $request->query->get('q', '');
        $order = $request->query->get('order', 'desc'); // 'desc' par défaut

        $qb = $interventionRepository->createQueryBuilder('i');
        if ($query) {
            $qb->where('i.technicien LIKE :query OR i.typeint LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }
        $qb->orderBy('i.idint', $order === 'asc' ? 'ASC' : 'DESC');

        $interventions = $qb->getQuery()->getResult();

        return $this->render('intervention/_list.html.twig', [
            'interventions' => $interventions,
        ]);
    }

    #[Route('/{idint}', name: 'app_intervention_show', methods: ['GET'])]
    public function show(Intervention $intervention): Response
    {
        return $this->render('intervention/show.html.twig', [
            'intervention' => $intervention,
        ]);
    }

    #[Route('/{idint}/edit', name: 'app_intervention_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Intervention $intervention, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InterventionForm::class, $intervention);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intervention/edit.html.twig', [
            'intervention' => $intervention,
            'form' => $form,
        ]);
    }

    #[Route('/{idint}', name: 'app_intervention_delete', methods: ['POST'])]
    public function delete(Request $request, Intervention $intervention, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$intervention->getIdint(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($intervention);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
    }
}
