<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\EntreestockRepository;
use App\Repository\SortiestockRepository;
use App\Repository\EquipementRepository;
use App\Repository\InterventionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        ArticleRepository $articleRepository,
        EntreestockRepository $entreestockRepository,
        SortiestockRepository $sortiestockRepository,
        EquipementRepository $equipementRepository,
        InterventionRepository $interventionRepository
    ): Response {
        $articles = $articleRepository->findAllWithStock();
        
        // Statistiques générales
        $stats = [
            'total_articles' => count($articles),
            'total_equipements' => count($equipementRepository->findAll()),
            'total_interventions' => count($interventionRepository->findAll()),
            'articles_stock_critique' => 0,
            'articles_stock_alerte' => 0,
            'valeur_stock_total' => 0,
        ];
        
        // Calcul des statistiques de stock
        foreach ($articles as $article) {
            if ($article->isStockCritique()) {
                $stats['articles_stock_critique']++;
            } elseif ($article->isStockEnAlerte()) {
                $stats['articles_stock_alerte']++;
            }
            
            // Calcul de la valeur du stock
            foreach ($article->getEntreestocks() as $entree) {
                $stats['valeur_stock_total'] += $entree->getQuantite() * $entree->getPrixu();
            }
        }
        
        // Articles avec stock critique
        $articlesCritiques = array_filter($articles, function($article) {
            return $article->isStockCritique();
        });
        
        // Dernières entrées et sorties
        $dernieresEntrees = $entreestockRepository->findRecentWithArticles(5);
        $dernieresSorties = $sortiestockRepository->findRecentWithArticles(5);
        
        // Données pour le graphique (Chart.js)
        $labels = [];
        $entrees = [];
        $sorties = [];
        foreach ($articles as $article) {
            $labels[] = $article->getNomart();
            $entrees[] = $entreestockRepository->count(['idart' => $article]);
            $sorties[] = $sortiestockRepository->count(['idart' => $article]);
        }
        
        // Statistiques équipements par type
        $equipements = $equipementRepository->findAll();
        $types = [];
        $etats = [];
        foreach ($equipements as $eq) {
            $type = $eq->getTypeeq() ?: 'Inconnu';
            $etat = $eq->getEtat() ?: 'Inconnu';
            $types[$type] = ($types[$type] ?? 0) + 1;
            $etats[$etat] = ($etats[$etat] ?? 0) + 1;
        }
        $typeLabels = array_keys($types);
        $typeValues = array_values($types);
        $etatLabels = array_keys($etats);
        $etatValues = array_values($etats);
        
        return $this->render('dashboard/index.html.twig', [
            'stats' => $stats,
            'articles_critiques' => $articlesCritiques,
            'dernieres_entrees' => $dernieresEntrees,
            'dernieres_sorties' => $dernieresSorties,
            'labels' => $labels,
            'entrees' => $entrees,
            'sorties' => $sorties,
            'equip_types' => $types,
            'equip_etats' => $etats,
            'type_labels' => $typeLabels,
            'type_values' => $typeValues,
            'etat_labels' => $etatLabels,
            'etat_values' => $etatValues,
        ]);
    }

    #[Route('/rapport-stock', name: 'app_dashboard_rapport_stock')]
    public function rapportStock(
        ArticleRepository $articleRepository,
        EntreestockRepository $entreestockRepository,
        SortiestockRepository $sortiestockRepository
    ): Response {
        $articles = $articleRepository->findAllWithStock();

        // Calculs pour le rapport
        $rapportData = [];
        $totalValeurStock = 0;

        foreach ($articles as $article) {
            $stockActuel = $article->getStockActuel();
            $valeurStock = 0;

            // Calcul de la valeur moyenne du stock
            $totalQuantiteEntree = 0;
            $totalValeurEntree = 0;

            foreach ($article->getEntreestocks() as $entree) {
                $totalQuantiteEntree += $entree->getQuantite();
                $totalValeurEntree += $entree->getQuantite() * $entree->getPrixu();
            }

            $prixMoyen = $totalQuantiteEntree > 0 ? $totalValeurEntree / $totalQuantiteEntree : 0;
            $valeurStock = $stockActuel * $prixMoyen;
            $totalValeurStock += $valeurStock;

            $rapportData[] = [
                'article' => $article,
                'stock_actuel' => $stockActuel,
                'prix_moyen' => $prixMoyen,
                'valeur_stock' => $valeurStock,
                'total_entrees' => $totalQuantiteEntree,
                'total_sorties' => array_sum(array_map(fn($s) => $s->getQuantite(), $article->getSortiestocks()->toArray())),
            ];
        }

        // Trier par valeur de stock décroissante
        usort($rapportData, function($a, $b) {
            return $b['valeur_stock'] <=> $a['valeur_stock'];
        });

        return $this->render('dashboard/rapport_stock.html.twig', [
            'rapport_data' => $rapportData,
            'total_valeur_stock' => $totalValeurStock,
        ]);
    }
}
