<?php
// src/Service/IperiusMailProcessor.php
namespace App\Service;

use App\Entity\BackupLog;
use Doctrine\ORM\EntityManagerInterface;

class IperiusMailProcessor
{
    private $entityManager;
    private $imapHost = '{mail.c2e-cablage.com:993/imap/ssl}INBOX';
    private $imapUser = 'hamza.farhani@esprit.Tn';
    private $imapPass = 'Azerty123!';

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function processNewReports(): int
    {
        $inbox = imap_open($this->imapHost, $this->imapUser, $this->imapPass);
        $emails = imap_search($inbox, 'UNSEEN SUBJECT "SauvegardeDonnees"');
        $processedCount = 0;

        if ($emails) {
            foreach ($emails as $emailId) {
                $header = imap_headerinfo($inbox, $emailId);
                $body = imap_body($inbox, $emailId);
                
                if ($backupLog = $this->createBackupLogFromEmail($body)) {
                    $this->entityManager->persist($backupLog);
                    $processedCount++;
                }
                
                imap_setflag_full($inbox, $emailId, "\\Seen");
            }
            
            $this->entityManager->flush();
        }

        imap_close($inbox);
        return $processedCount;
    }

    private function createBackupLogFromEmail(string $emailBody): ?BackupLog
    {
        // Extraction des données depuis le format Iperius
        $pattern = '/Désmarrage de la sauvegarde\s*\|\s*([^\n]+).*?Fin de la sauvegarde\s*\|\s*([^\n]+).*?Titre des données cessées\s*\|\s*([^\n]+)/s';
        
        if (preg_match($pattern, $emailBody, $matches)) {
            $backupLog = new BackupLog();
            
            // Nettoyage et conversion des dates
            $startDate = str_replace(['.', '/'], '-', trim($matches[1]));
            $endDate = str_replace(['.', '/'], '-', trim($matches[2]));
            
            $backupLog->setBackupName('SauvegardeDonnees');
            $backupLog->setStartTime(new \DateTime($startDate));
            $backupLog->setEndTime(new \DateTime($endDate));
            $backupLog->setStatus('completed');
            
            // Extraction taille (ex: "2,5 GB" => 2.5)
            $size = (float) str_replace(',', '.', trim(str_replace('GB', '', $matches[3])));
            $backupLog->setTotalSizeGB($size);
            
            return $backupLog;
        }
        
        return null;
    }
}