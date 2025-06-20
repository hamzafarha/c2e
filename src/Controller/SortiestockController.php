<?php

namespace App\Controller;

use App\Entity\Sortiestock;
use App\Entity\Article;
use App\Form\SortiestockType;
use App\Repository\SortiestockRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortiestock')]
final class SortiestockController extends AbstractController
{
    #[Route(name: 'app_sortiestock_index', methods: ['GET'])]
    public function index(SortiestockRepository $sortiestockRepository): Response
    {
        return $this->render('sortiestock/index.html.twig', [
            'sortiestocks' => $sortiestockRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_sortiestock_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ArticleRepository $articleRepository): Response
    {
        $sortiestock = new Sortiestock();

        // Pré-sélection d'un article si passé en paramètre
        $articleId = $request->query->get('article');
        if ($articleId) {
            $article = $articleRepository->find($articleId);
            if ($article) {
                $sortiestock->setIdart($article);
            }
        }

        // Définir la date de sortie par défaut à aujourd'hui
        $sortiestock->setDatesortie(new \DateTime());

        $form = $this->createForm(SortiestockType::class, $sortiestock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification du stock disponible
            $article = $sortiestock->getIdart();
            $stockActuel = $article->getStockActuel();
            $quantiteDemandee = $sortiestock->getQuantite();

            if ($quantiteDemandee > $stockActuel) {
                $this->addFlash('error', sprintf(
                    'Stock insuffisant ! Stock disponible : %d, Quantité demandée : %d',
                    $stockActuel,
                    $quantiteDemandee
                ));

                return $this->render('sortiestock/new.html.twig', [
                    'sortiestock' => $sortiestock,
                    'form' => $form,
                    'articles' => $articleRepository->findAllWithStock(),
                ]);
            }

            $entityManager->persist($sortiestock);
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                'Sortie de stock enregistrée : %d unités de %s',
                $quantiteDemandee,
                $article->getRefart()
            ));

            return $this->redirectToRoute('app_sortiestock_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortiestock/new.html.twig', [
            'sortiestock' => $sortiestock,
            'form' => $form,
            'articles' => $articleRepository->findAllWithStock(),
        ]);
    }

    #[Route('/{id}', name: 'app_sortiestock_show', methods: ['GET'])]
    public function show(Sortiestock $sortiestock): Response
    {
        return $this->render('sortiestock/show.html.twig', [
            'sortiestock' => $sortiestock,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sortiestock_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortiestock $sortiestock, EntityManagerInterface $entityManager): Response
    {
        $quantiteOriginale = $sortiestock->getQuantite();
        $form = $this->createForm(SortiestockType::class, $sortiestock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification du stock si la quantité a changé
            $nouvelleQuantite = $sortiestock->getQuantite();
            if ($nouvelleQuantite != $quantiteOriginale) {
                $article = $sortiestock->getIdart();
                $stockActuel = $article->getStockActuel() + $quantiteOriginale; // Remettre l'ancienne quantité

                if ($nouvelleQuantite > $stockActuel) {
                    $this->addFlash('error', sprintf(
                        'Stock insuffisant ! Stock disponible : %d, Quantité demandée : %d',
                        $stockActuel,
                        $nouvelleQuantite
                    ));

                    return $this->render('sortiestock/edit.html.twig', [
                        'sortiestock' => $sortiestock,
                        'form' => $form,
                    ]);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Sortie de stock modifiée avec succès');

            return $this->redirectToRoute('app_sortiestock_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortiestock/edit.html.twig', [
            'sortiestock' => $sortiestock,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sortiestock_delete', methods: ['POST'])]
    public function delete(Request $request, Sortiestock $sortiestock, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sortiestock->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sortiestock);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sortiestock_index', [], Response::HTTP_SEE_OTHER);
    }
}
