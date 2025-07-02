<?php

namespace App\Entity;

use App\Repository\BackupLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BackupLogRepository::class)]
class BackupLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $startTime = null;

    #[ORM\Column]
    private ?\DateTime $endTime = null;

    #[ORM\Column(length: 255)]
    private ?string $duration = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    private ?string $totalSize = null;

    #[ORM\Column]
    private ?int $filesProcessed = null;

    #[ORM\Column]
    private ?int $errors = null;

    #[ORM\Column(nullable: true)]
    private ?int $objectsDeleted = null;

    #[ORM\Column(length: 255)]
    private ?string $sourcePath = null;

    #[ORM\Column(length: 255)]
    private ?string $destinationPath = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartTime(): ?\DateTime
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTime $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTime
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTime $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): static
    {
        $this->duration = $duration;

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

    public function getTotalSize(): ?string
    {
        return $this->totalSize;
    }

    public function setTotalSize(string $totalSize): static
    {
        $this->totalSize = $totalSize;

        return $this;
    }

    public function getFilesProcessed(): ?int
    {
        return $this->filesProcessed;
    }

    public function setFilesProcessed(int $filesProcessed): static
    {
        $this->filesProcessed = $filesProcessed;

        return $this;
    }

    public function getErrors(): ?int
    {
        return $this->errors;
    }

    public function setErrors(int $errors): static
    {
        $this->errors = $errors;

        return $this;
    }

    public function getObjectsDeleted(): ?int
    {
        return $this->objectsDeleted;
    }

    public function setObjectsDeleted(?int $objectsDeleted): static
    {
        $this->objectsDeleted = $objectsDeleted;

        return $this;
    }

    public function getSourcePath(): ?string
    {
        return $this->sourcePath;
    }

    public function setSourcePath(string $sourcePath): static
    {
        $this->sourcePath = $sourcePath;

        return $this;
    }

    public function getDestinationPath(): ?string
    {
        return $this->destinationPath;
    }

    public function setDestinationPath(string $destinationPath): static
    {
        $this->destinationPath = $destinationPath;

        return $this;
    }
}
