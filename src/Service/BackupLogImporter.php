<?php
namespace App\Service;

use App\Entity\BackupLog;
use Doctrine\ORM\EntityManagerInterface;

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
     * Parse un fichier de log Iperius et retourne un BackupLog ou null
     * @param string $filePath
     * @return BackupLog|null
     */
    public function parseLogFile(string $filePath): ?BackupLog
    {
        $content = file_get_contents($filePath);
        // Parser simple, à adapter selon le format réel
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