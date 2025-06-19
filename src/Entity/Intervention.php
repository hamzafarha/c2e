<?php

namespace App\Entity;

use App\Repository\InterventionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
class Intervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idint')]
    private ?int $idint = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateint = null;

    #[ORM\Column(length: 255)]
    private ?string $typeint = null;

    #[ORM\Column(length: 255)]
    private ?string $technicien = null;

    #[ORM\Column(length: 255)]
    private ?string $etatapres = null;

    #[ORM\Column(length: 255)]
    private ?string $prochainedate = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    #[ORM\JoinColumn(name: 'ideq', referencedColumnName: 'ideq', nullable: false)]
    private ?Equipement $equipement = null;

    public function getIdint(): ?int
    {
        return $this->idint;
    }

    public function getDateint(): ?\DateTimeInterface
    {
        return $this->dateint;
    }

    public function setDateint(\DateTimeInterface $dateint): static
    {
        $this->dateint = $dateint;

        return $this;
    }

    public function getTypeint(): ?string
    {
        return $this->typeint;
    }

    public function setTypeint(string $typeint): static
    {
        $this->typeint = $typeint;

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

    public function getEtatapres(): ?string
    {
        return $this->etatapres;
    }

    public function setEtatapres(string $etatapres): static
    {
        $this->etatapres = $etatapres;

        return $this;
    }

    public function getProchainedate(): ?string
    {
        return $this->prochainedate;
    }

    public function setProchainedate(string $prochainedate): static
    {
        $this->prochainedate = $prochainedate;

        return $this;
    }

    public function getEquipement(): ?int
    {
        return $this->equipement;
    }

    public function setEquipement(?Equipement $equipement): static
    {
        $this->equipement = $equipement;

        return $this;
    }
}
