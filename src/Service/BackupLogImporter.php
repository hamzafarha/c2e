<?php

namespace App\Service;

use App\Entity\BackupLog;
use Doctrine\ORM\EntityManagerInterface;
use eXorus\PhpMimeMailParser\Parser as PhpMimeMailParserParser;
use PhpMimeMailParser\Parser;

class BackupLogImporter
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Importe tous les fichiers de log d'un dossier
     * @param string $directory
     * @return int Nombre de logs importés
     */
    public function importFromDirectory(string $directory): int
    {
        $imported = 0;
        $files = glob($directory . '/*.{txt,html}', GLOB_BRACE);
        foreach ($files as $file) {
            $log = $this->parseLogFile($file);
            if ($log) {
                $this->em->persist($log);
                $imported++;
            }
        }
        $this->em->flush();
        return $imported;
    }

    /**
     * Importe un rapport de sauvegarde depuis un fichier .eml
     * @param string $emlPath
     * @return BackupLog|null
     */
    public function importFromEml(string $emlPath): ?BackupLog
    {
        try {
            // Lire le contenu brut du fichier .eml
            $rawEmail = file_get_contents($emlPath);

            // Solution simple pour extraire le corps texte
            if (preg_match('/Content-Type: text\/plain;.*?\n\n(.*?)\n--/s', $rawEmail, $matches)) {
                $body = trim($matches[1]);
            } elseif (preg_match('/Content-Type: text\/plain;.*?\n\n(.*)/s', $rawEmail, $matches)) {
                $body = trim($matches[1]);
            } else {
                // Fallback: prendre tout après les headers
                $parts = explode("\n\n", $rawEmail, 2);
                $body = count($parts) > 1 ? trim($parts[1]) : $rawEmail;
            }

            // Nettoyer le corps si nécessaire
            $body = $this->cleanEmailBody($body);

            if (empty($body)) {
                throw new \RuntimeException("Le corps de l'email est vide ou non lisible.");
            }

            $log = $this->parseLogContent($body);

            if (!$log) {
                throw new \RuntimeException("Impossible d'extraire les données de sauvegarde depuis l'email.");
            }

            $this->em->persist($log);
            $this->em->flush();

            return $log;
        } catch (\Exception $e) {
            throw new \RuntimeException("Erreur lors du parsing de l'email: " . $e->getMessage());
        }
    }

    private function cleanEmailBody(string $body): string
    {
        // Supprimer les lignes de séparation
        $body = preg_replace('/-----Original Message-----.*/s', '', $body);
        $body = preg_replace('/-- \n.*/s', '', $body);

        // Supprimer les réponses précédentes
        $body = preg_replace('/On.*wrote:.*/s', '', $body);

        return trim($body);
    }

    /**
     * Parse le contenu brut d'un rapport de sauvegarde (texte ou HTML)
     * @param string $content
     * @return BackupLog|null
     */
    public function parseLogContent(string $content): ?BackupLog
    {
        $startTime = $this->extractDate($content, '/Start time: ([^\n]+)/i');
        $endTime = $this->extractDate($content, '/End time: ([^\n]+)/i');
        $duration = $this->extractString($content, '/Duration: ([^\n]+)/i');
        $status = $this->extractString($content, '/Status: ([^\n]+)/i');
        $totalSize = $this->extractString($content, '/Total size: ([^\n]+)/i');
        $filesProcessed = $this->extractInt($content, '/Files processed: (\d+)/i');
        $errors = $this->extractInt($content, '/Errors: (\d+)/i');
        $objectsDeleted = $this->extractInt($content, '/Objects deleted: (\d+)/i', true);
        $sourcePath = $this->extractString($content, '/Source: ([^\n]+)/i');
        $destinationPath = $this->extractString($content, '/Destination: ([^\n]+)/i');

        if (!$startTime || !$endTime) {
            return null;
        }

        $log = new BackupLog();
        $log->setStartTime($startTime);
        $log->setEndTime($endTime);
        $log->setDuration($duration ?? '');
        $log->setStatus($status ?? '');
        $log->setTotalSize($totalSize ?? '');
        $log->setFilesProcessed($filesProcessed ?? 0);
        $log->setErrors($errors ?? 0);
        $log->setObjectsDeleted($objectsDeleted);
        $log->setSourcePath($sourcePath ?? '');
        $log->setDestinationPath($destinationPath ?? '');
        return $log;
    }

    /**
     * Parse un fichier de log Iperius et retourne un BackupLog ou null
     * @param string $filePath
     * @return BackupLog|null
     */
    public function parseLogFile(string $filePath): ?BackupLog
    {
        $content = file_get_contents($filePath);
        return $this->parseLogContent($content);
    }

    private function extractDate(string $content, string $pattern): ?\DateTime
    {
        if (preg_match($pattern, $content, $matches)) {
            return new \DateTime(trim($matches[1]));
        }
        return null;
    }

    private function extractString(string $content, string $pattern): ?string
    {
        if (preg_match($pattern, $content, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    private function extractInt(string $content, string $pattern, bool $nullable = false): ?int
    {
        if (preg_match($pattern, $content, $matches)) {
            return (int) $matches[1];
        }
        return $nullable ? null : 0;
    }
}
