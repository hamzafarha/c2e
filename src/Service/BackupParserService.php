<?php

namespace App\Service;

class BackupParserService
{
    function parseEmlBody($filePath) {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        $bodyStarted = false;
        $bodyLines = [];
    
        foreach ($lines as $line) {
            if ($bodyStarted) {
                $bodyLines[] = $line;
            } elseif (trim($line) === '') {
                $bodyStarted = true;
            }
        }
    
        return implode("\n", $bodyLines); // corps du mail complet
    }
    

    public function parseIperiusReportFromLines(array $lines): array
    {
        $data = [];

        foreach ($lines as $line) {
            if (preg_match('/Démarrage de la sauvegarde\s*:\s*(.*)/i', $line, $m)) {
                $data['start_time'] = \DateTime::createFromFormat('d/m/Y H:i:s', trim($m[1]));
            }
            if (preg_match('/Fin de la sauvegarde\s*:\s*(.*)/i', $line, $m)) {
                $data['end_time'] = \DateTime::createFromFormat('d/m/Y H:i:s', trim($m[1]));
            }
            if (preg_match('/Résultat\s*:\s*(.*)/i', $line, $m)) {
                $data['result'] = trim($m[1]);
            }
            if (preg_match('/Dossier source\s*:\s*(.*)/i', $line, $m)) {
                $data['source_path'] = trim($m[1]);
            }
            if (preg_match('/Destination\s*:\s*(.*)/i', $line, $m)) {
                $data['destination_path'] = trim($m[1]);
            }
            if (preg_match('/Taille des fichiers copiés\s*:\s*(.*)/i', $line, $m)) {
                $data['size'] = trim($m[1]);
            }
        }

        return $data;
    }
}
