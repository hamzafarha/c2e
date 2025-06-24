<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;

class ChatbotController extends AbstractController
{
    #[Route('/chatbot', name: 'chatbot', methods: ['POST'])]
    public function chatbot(Request $request, ArticleRepository $articleRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $question = strtolower($data['question'] ?? '');

        // Réponse à "Que dois-je commander ce mois-ci ?"
        if (strpos($question, 'commander') !== false) {
            $json = shell_exec('C:\\Users\\user\\anaconda3\\python C:\\Users\\user\\c2e_app\\scripts\\recommandation.py 2>&1');
            $recommandations = json_decode($json, true);
            if (!is_array($recommandations)) {
                return new JsonResponse(['answer' => "Erreur lors de l'exécution du script Python.<br><pre>" . htmlspecialchars($json) . "</pre>"]);
            }
            $to_order = array_filter($recommandations, fn($r) => $r['suggestion_commande'] > 0);
            if (count($to_order) === 0) {
                $answer = "Aucune commande recommandée ce mois-ci.";
            } else {
                $answer = "Commandes recommandées :<ul>";
                foreach ($to_order as $rec) {
                    $answer .= "<li>{$rec['reference']} : {$rec['suggestion_commande']}</li>";
                }
                $answer .= "</ul>";
            }
            return new JsonResponse(['answer' => $answer]);
        }

        // Réponse à "Combien reste-t-il de câbles réseau ?"
        if (strpos($question, 'câble') !== false || strpos($question, 'cable') !== false) {
            $articles = $articleRepo->createQueryBuilder('a')
                ->where('a.nomart LIKE :mot OR a.refart LIKE :mot')
                ->setParameter('mot', '%câble%')
                ->getQuery()->getResult();
            if (!$articles) {
                return new JsonResponse(['answer' => "Aucun article 'câble' trouvé."]);
            }
            $answer = "";
            foreach ($articles as $a) {
                $answer .= "{$a->getRefart()} ({$a->getNomart()}) : {$a->getStockActuel()}<br>";
            }
            return new JsonResponse(['answer' => $answer]);
        }

        // Réponse par défaut
        return new JsonResponse(['answer' => "Je ne comprends pas encore cette question. Essayez :<br>- Que dois-je commander ce mois-ci ?<br>- Combien reste-t-il de câbles réseau ?"]);
    }
} 