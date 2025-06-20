<?php

namespace App\Entity;

use App\Repository\SortiestockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SortiestockRepository::class)]
class Sortiestock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $datesortie = null;

    #[ORM\Column(length: 255)]
    private ?string $technicien = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\ManyToOne(inversedBy: 'sortiestocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $idart = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatesortie(): ?\DateTime
    {
        return $this->datesortie;
    }

    public function setDatesortie(\DateTime $datesortie): static
    {
        $this->datesortie = $datesortie;

        return $this;
    }

    public function getTechnicien(): ?string
    {
        return $this->technicien;
    }

    public function setTechnicien(string $technicien): static
    {
        $this->technicien = $technicien;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getIdart(): ?Article
    {
        return $this->idart;
    }

    public function setIdart(?Article $idart): static
    {
        $this->idart = $idart;

        return $this;
    }
}
