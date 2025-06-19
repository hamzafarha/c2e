<?php

namespace App\Controller;

use App\Entity\Entreestock;
use App\Form\EntreestockType;
use App\Repository\EntreestockRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/entreestock')]
final class EntreestockController extends AbstractController
{
    #[Route(name: 'app_entreestock_index', methods: ['GET'])]
    public function index(EntreestockRepository $entreestockRepository): Response
    {
        return $this->render('entreestock/index.html.twig', [
            'entreestocks' => $entreestockRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_entreestock_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ArticleRepository $articleRepository): Response
    {
        $entreestock = new Entreestock();

        // Pré-sélection d'un article si passé en paramètre
        $articleId = $request->query->get('article');
        if ($articleId) {
            $article = $articleRepository->find($articleId);
            if ($article) {
                $entreestock->setIdart($article);
            }
        }

        // Définir la date d'entrée par défaut à aujourd'hui
        $entreestock->setDateentree(new \DateTime());

        $form = $this->createForm(EntreestockType::class, $entreestock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($entreestock);
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                'Entrée de stock enregistrée : %d unités de %s',
                $entreestock->getQuantite(),
                $entreestock->getIdart()->getRefart()
            ));

            return $this->redirectToRoute('app_entreestock_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('entreestock/new.html.twig', [
            'entreestock' => $entreestock,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_entreestock_show', methods: ['GET'])]
    public function show(Entreestock $entreestock): Response
    {
        return $this->render('entreestock/show.html.twig', [
            'entreestock' => $entreestock,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_entreestock_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Entreestock $entreestock, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EntreestockType::class, $entreestock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Entrée de stock modifiée avec succès');

            return $this->redirectToRoute('app_entreestock_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('entreestock/edit.html.twig', [
            'entreestock' => $entreestock,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_entreestock_delete', methods: ['POST'])]
    public function delete(Request $request, Entreestock $entreestock, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$entreestock->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($entreestock);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_entreestock_index', [], Response::HTTP_SEE_OTHER);
    }
}
