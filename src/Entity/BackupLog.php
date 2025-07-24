<?php
// src/Entity/BackupLog.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class BackupLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $backupName = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $details = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $totalSizeGB = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $filesProcessed = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $errorsCount = null;

    #[ORM\Column(length: 255)]
    private ?string $backupType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sourcePath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $destinationPath = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $duration = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBackupName(): ?string
    {
        return $this->backupName;
    }

    public function setBackupName(string $backupName): static
    {
        $this->backupName = $backupName;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getTotalSizeGB(): ?float
    {
        return $this->totalSizeGB;
    }

    public function setTotalSizeGB(?float $totalSizeGB): static
    {
        $this->totalSizeGB = $totalSizeGB;

        return $this;
    }

    public function getFilesProcessed(): ?int
    {
        return $this->filesProcessed;
    }

    public function setFilesProcessed(?int $filesProcessed): static
    {
        $this->filesProcessed = $filesProcessed;

        return $this;
    }

    public function getErrorsCount(): ?int
    {
        return $this->errorsCount;
    }

    public function setErrorsCount(?int $errorsCount): static
    {
        $this->errorsCount = $errorsCount;

        return $this;
    }

    public function getBackupType(): ?string
    {
        return $this->backupType;
    }

    public function setBackupType(string $backupType): static
    {
        $this->backupType = $backupType;

        return $this;
    }

    public function getSourcePath(): ?string
    {
        return $this->sourcePath;
    }

    public function setSourcePath(?string $sourcePath): static
    {
        $this->sourcePath = $sourcePath;

        return $this;
    }

    public function getDestinationPath(): ?string
    {
        return $this->destinationPath;
    }

    public function setDestinationPath(?string $destinationPath): static
    {
        $this->destinationPath = $destinationPath;

        return $this;
    }

    public function getDuration(): ?string
    {
        if ($this->startTime && $this->endTime) {
            $interval = $this->startTime->diff($this->endTime);
            return $interval->format('%Hh %Im %Ss');
        }
        return null;
    }

    public function setDuration(string $duration): static
    {
        $this->duration = $duration;
        return $this;
    }
}
