<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomart = null;

    #[ORM\Column(length: 255)]
    private ?string $refart = null;

    #[ORM\Column]
    private ?int $seuilalerte = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, Entreestock>
     */
    #[ORM\OneToMany(targetEntity: Entreestock::class, mappedBy: 'idart')]
    private Collection $entreestocks;

    /**
     * @var Collection<int, Sortiestock>
     */
    #[ORM\OneToMany(targetEntity: Sortiestock::class, mappedBy: 'idart', orphanRemoval: true)]
    private Collection $sortiestocks;

    public function __construct()
    {
        $this->entreestocks = new ArrayCollection();
        $this->sortiestocks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomart(): ?string
    {
        return $this->nomart;
    }

    public function setNomart(string $nomart): static
    {
        $this->nomart = $nomart;

        return $this;
    }

    public function getRefart(): ?string
    {
        return $this->refart;
    }

    public function setRefart(string $refart): static
    {
        $this->refart = $refart;

        return $this;
    }

    public function getSeuilalerte(): ?int
    {
        return $this->seuilalerte;
    }

    public function setSeuilalerte(int $seuilalerte): static
    {
        $this->seuilalerte = $seuilalerte;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Entreestock>
     */
    public function getEntreestocks(): Collection
    {
        return $this->entreestocks;
    }

    public function addEntreestock(Entreestock $entreestock): static
    {
        if (!$this->entreestocks->contains($entreestock)) {
            $this->entreestocks->add($entreestock);
            $entreestock->setIdart($this);
        }

        return $this;
    }

    public function removeEntreestock(Entreestock $entreestock): static
    {
        if ($this->entreestocks->removeElement($entreestock)) {
            // set the owning side to null (unless already changed)
            if ($entreestock->getIdart() === $this) {
                $entreestock->setIdart(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sortiestock>
     */
    public function getSortiestocks(): Collection
    {
        return $this->sortiestocks;
    }

    public function addSortiestock(Sortiestock $sortiestock): static
    {
        if (!$this->sortiestocks->contains($sortiestock)) {
            $this->sortiestocks->add($sortiestock);
            $sortiestock->setIdart($this);
        }

        return $this;
    }

    public function removeSortiestock(Sortiestock $sortiestock): static
    {
        if ($this->sortiestocks->removeElement($sortiestock)) {
            // set the owning side to null (unless already changed)
            if ($sortiestock->getIdart() === $this) {
                $sortiestock->setIdart(null);
            }
        }

        return $this;
    }
}
