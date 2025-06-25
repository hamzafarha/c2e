<?php
// StockRecommandationController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StockRecommandationController extends AbstractController
{
    #[Route('/stock/recommandations', name: 'stock_recommandations')]
    public function index(): Response
    {
        $python = 'C:\\Users\\user\\anaconda3\\python';
        $script = 'C:\\Users\\user\\c2e_app\\scripts\\recommandation.py';
        $json = shell_exec("$python $script 2>&1");
        $data = json_decode($json, true);

        if ($data === null) {
            return new Response("Erreur dans le traitement du script Python.<br><pre>" . htmlspecialchars($json) . "</pre>");
        }

        return $this->render('stock/recommandations.html.twig', [
            'recommandations' => $data,
        ]);
    }
}
 