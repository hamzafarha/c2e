<?php
// src/Service/IperiusParserService.php
namespace App\Service;

class IperiusParserService
{
    public function parseReportContent(string $content): array
    {
        $lines = explode("\n", $content);
        $data = [];

        foreach ($lines as $line) {
            $this->parseLine($line, $data);
        }

        return $this->normalizeData($data);
    }

    private function parseLine(string $line, array &$data): void
    {
        $patterns = [
            '/Démarrage de la sauvegarde\s*:\s*(.+)/i' => 'start_time',
            '/Fin de la sauvegarde\s*:\s*(.+)/i' => 'end_time',
            '/Taille des fichiers copiés\s*:\s*(.+)/i' => 'total_size',
            '/Fichiers traités\s*:\s*(\d+)/i' => 'files_processed',
            '/Résultat\s*:\s*(.+)/i' => 'status',
            '/Dossier source\s*:\s*(.+)/i' => 'source_path',
            '/Destination\s*:\s*(.+)/i' => 'destination_path'
        ];

        foreach ($patterns as $pattern => $key) {
            if (preg_match($pattern, $line, $matches)) {
                $data[$key] = trim($matches[1]);
                break;
            }
        }
    }

    private function normalizeData(array $data): array
    {
        // Conversion des dates
        if (isset($data['start_time'])) {
            $data['start_time'] = \DateTime::createFromFormat('d/m/Y H:i:s', $data['start_time']);
        }
        if (isset($data['end_time'])) {
            $data['end_time'] = \DateTime::createFromFormat('d/m/Y H:i:s', $data['end_time']);
        }

        // Conversion de la taille
        if (isset($data['total_size'])) {
            $data['total_size_gb'] = (float)str_replace(',', '.', preg_replace('/[^\d,]/', '', $data['total_size']));
        }

        return $data;
    }
}