<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\EntreestockRepository;
use App\Repository\SortiestockRepository;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StockStatsController extends AbstractController
{
    #[Route('/stock/stats', name: 'stock_stats')]
    public function stats(
        ArticleRepository $articleRepo,
        EntreestockRepository $entreeRepo,
        SortiestockRepository $sortieRepo
    ): Response {
        $articles = $articleRepo->findAll();
        $labels = [];
        $entrees = [];
        $sorties = [];
        foreach ($articles as $article) {
            $labels[] = $article->getNomart();
            $entrees[] = $entreeRepo->count(['idart' => $article]);
            $sorties[] = $sortieRepo->count(['idart' => $article]);
        }
        return $this->render('stock/stats.html.twig', [
            'labels' => $labels,
            'entrees' => $entrees,
            'sorties' => $sorties,
        ]);
    }

    #[Route('/stock/export-pdf', name: 'stock_export_pdf')]
    public function exportPdf(ArticleRepository $articleRepo): Response
    {
        $articles = $articleRepo->findAll();
        $html = $this->renderView('stock/pdf.html.twig', [
            'articles' => $articles,
        ]);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="stock.pdf"'
            ]
        );
    }
} 