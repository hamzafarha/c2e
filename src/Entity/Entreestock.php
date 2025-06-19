<?php

namespace App\Entity;

use App\Repository\EntreestockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntreestockRepository::class)]
class Entreestock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateentree = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column]
    private ?float $prixu = null;

    #[ORM\Column(length: 255)]
    private ?string $fournisseur = null;

    #[ORM\ManyToOne(inversedBy: 'entreestocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $idart = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateentree(): ?\DateTime
    {
        return $this->dateentree;
    }

    public function setDateentree(\DateTime $dateentree): static
    {
        $this->dateentree = $dateentree;

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

    public function getPrixu(): ?float
    {
        return $this->prixu;
    }

    public function setPrixu(float $prixu): static
    {
        $this->prixu = $prixu;

        return $this;
    }

    public function getFournisseur(): ?string
    {
        return $this->fournisseur;
    }

    public function setFournisseur(string $fournisseur): static
    {
        $this->fournisseur = $fournisseur;

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
