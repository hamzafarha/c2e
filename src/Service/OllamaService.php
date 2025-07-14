<?php

// src/Service/OllamaService.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OllamaService
{
    private HttpClientInterface $client;
    private string $ollamaUrl;

    public function __construct(HttpClientInterface $client, string $ollamaUrl = 'http://localhost:11434')
    {
        $this->client = $client;
        $this->ollamaUrl = $ollamaUrl;
    }

    public function parseEmailWithOllama(string $emailContent): array
    {
        $prompt = <<<PROMPT
        Tu es un expert en analyse de rapports de sauvegarde Iperius. 
        Extrais précisément les informations suivantes depuis cet email:

        ### Champs obligatoires:
        - date_debut: "YYYY-MM-DD HH:MM:SS" (recherche "Date/heure début")
        - date_fin: "YYYY-MM-DD HH:MM:SS" (recherche "Date/heure fin")
        - statut: "succès", "échec partiel" ou "échec total"
        - taille_totale: en GB ou MB (recherche "Taille totale")
        - nb_fichiers: entier (recherche "Fichiers copiés")
        - chemin_source: chemin complet
        - chemin_destination: chemin complet

        ### Champs optionnels:
        - duree: "HH:MM:SS"
        - nb_erreurs: entier
        - nb_objets_supprimes: entier

        Format de sortie JSON uniquement. Si une information est manquante, utilise null.
        
        Email:
        {$emailContent}
        
        Retourne uniquement le JSON, sans commentaires.
        PROMPT;

        try {
            $response = $this->client->request('POST', $this->ollamaUrl . '/api/generate', [
                'json' => [
                    'model' => 'mistral',
                    'prompt' => $prompt,
                    'format' => 'json',
                    'stream' => false,
                    'options' => ['temperature' => 0.3]
                ],
                'timeout' => 60 // Augmentez le timeout
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException('Erreur Ollama: ' . $response->getContent(false));
            }

            $data = json_decode($response->getContent(), true);

            if (!isset($data['response'])) {
                throw new \RuntimeException('Réponse Ollama incomplète');
            }

            $result = json_decode($data['response'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Réponse JSON invalide');
            }

            return $result;
        } catch (\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface $e) {
            throw new \RuntimeException('Erreur de connexion à Ollama: ' . $e->getMessage());
        }
    }
}
