<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpClient\HttpClient;

class ChatbotController extends AbstractController
{
    #[Route('/chatbot', name: 'chatbot', methods: ['POST'])]
    public function chatbot(Request $request, ArticleRepository $articleRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $question = strtolower($data['question'] ?? '');

        // Réponse aux salutations
        if (preg_match('/\b(bonjour|salut|hello|coucou)\b/', $question)) {
            return new JsonResponse(['answer' => "Bonjour 👋 ! Comment puis-je vous aider concernant le stock ou les équipements ?"]);
        }
        if (preg_match('/\bmerci\b/', $question)) {
            return new JsonResponse(['answer' => "Avec plaisir ! N'hésitez pas si vous avez d'autres questions sur le stock."]);
        }
        if (preg_match('/\b(au revoir|bye|à bientôt)\b/', $question)) {
            return new JsonResponse(['answer' => "Au revoir ! Je reste à votre disposition pour toute question sur le stock."]);
        }

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

        // Appel à l'API Gemini pour toute autre question
        $geminiApiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
        if (!$geminiApiKey) {
            return new JsonResponse(['answer' => "Clé API Gemini manquante. Ajoutez-la dans votre fichier .env."]);
        }
        $client = \Symfony\Component\HttpClient\HttpClient::create();
        $response = $client->request('POST', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $geminiApiKey, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "Tu es un assistant pour la gestion de stock, d'articles, d'entrées et de sorties. Réponds de façon claire et concise en français."],
                            ['text' => $data['question'] ?? ''],
                        ]
                    ]
                ]
            ],
        ]);
        $result = $response->toArray(false);
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $answer = nl2br($result['candidates'][0]['content']['parts'][0]['text']);
        } else {
            $errorMsg = isset($result['error']['message']) ? $result['error']['message'] : json_encode($result);
            $answer = "Je n'ai pas pu obtenir de réponse de Gemini.<br><pre>" . htmlspecialchars($errorMsg) . "</pre>";
        }
        return new JsonResponse(['answer' => $answer]);
    }
} 