<?php

// src/Service/BackupLogImporter.php
namespace App\Service;

use App\Entity\BackupLog;
use App\Service\OllamaService;

class BackupLogImporter
{
    private OllamaService $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    public function importFromEmail(string $emailContent): BackupLog
    {
        $data = $this->ollamaService->parseEmailWithOllama($emailContent);

        $log = new BackupLog();
        $log->setStartTime(new \DateTime($data['date_debut']));
        $log->setEndTime(new \DateTime($data['date_fin']));
        $log->setDuration($data['duree'] ?? '00:00:00');
        $log->setStatus($this->normalizeStatus($data['statut']));
        $log->setTotalSize($data['taille_totale'] ?? '0');
        $log->setFilesProcessed($data['nb_fichiers'] ?? 0);
        $log->setErrors($data['nb_erreurs'] ?? 0);
        $log->setObjectsDeleted($data['nb_objets_supprimes'] ?? null);
        $log->setSourcePath($data['chemin_source'] ?? 'Inconnu');
        $log->setDestinationPath($data['chemin_destination'] ?? 'Inconnu');

        return $log;
    }

    private function normalizeStatus(string $status): string
    {
        $status = strtolower(trim($status));
        
        return match (true) {
            str_contains($status, 'succès') => 'Success',
            str_contains($status, 'échec total') => 'Total Failure',
            str_contains($status, 'échec partiel') => 'Partial Failure',
            default => ucfirst($status)
        };
    }
}