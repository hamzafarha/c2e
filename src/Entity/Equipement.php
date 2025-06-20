<?php

namespace App\Entity;

use App\Repository\EquipementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipementRepository::class)]
class Equipement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'ideq')]
    private ?int $ideq = null;

    #[ORM\Column(length: 255)]
    private ?string $typeeq = null;

    #[ORM\Column(length: 255)]
    private ?string $nomeq = null;

    #[ORM\Column(length: 255)]
    private ?string $referenceeq = null;

    #[ORM\Column(length: 255)]
    private ?string $localisationeq = null;

    #[ORM\Column(length: 255)]
    private ?string $modeleeq = null;
    
    #[ORM\Column(length: 255)]
    private ?string $numserieeq = null;

    /**
     * @var Collection<int, Intervention>
     */
    #[ORM\OneToMany(targetEntity: Intervention::class, mappedBy: 'equipement')]
    private Collection $interventions;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    public function __construct()
    {
        $this->interventions = new ArrayCollection();
    }

    public function getId(): ?int
{
    return $this->ideq;
}

    public function getTypeeq(): ?string
    {
        return $this->typeeq;
    }
    

    public function setTypeeq(string $typeeq): static
    {
        $this->typeeq = $typeeq;

        return $this;
    }

    public function getNomeq(): ?string
    {
        return $this->nomeq;
    }

    public function setNomeq(string $nomeq): static
    {
        $this->nomeq = $nomeq;

        return $this;
    }

    public function getReferenceeq(): ?string
    {
        return $this->referenceeq;
    }

    public function setReferenceeq(string $referenceeq): static
    {
        $this->referenceeq = $referenceeq;

        return $this;
    }

    public function getLocalisationeq(): ?string
    {
        return $this->localisationeq;
    }

    public function setLocalisationeq(string $localisationeq): static
    {
        $this->localisationeq = $localisationeq;

        return $this;
    }

    public function getModeleeq(): ?string
    {
        return $this->modeleeq;
    }

    public function setModeleeq(string $modeleeq): static
    {
        $this->modeleeq = $modeleeq;

        return $this;
    }

    public function getNumserieeq(): ?string
    {
        return $this->numserieeq;
    }

    public function setNumserieeq(string $numserieeq): static
    {
        $this->numserieeq = $numserieeq;

        return $this;
    }

    /**
     * @return Collection<int, Intervention>
     */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    public function addIntervention(Intervention $intervention): static
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
            $intervention->setEquipement($this);
        }

        return $this;
    }

    public function removeIntervention(Intervention $intervention): static
    {
        if ($this->interventions->removeElement($intervention)) {
            // set the owning side to null (unless already changed)
            if ($intervention->getEquipement() === $this) {
                $intervention->setEquipement(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nomeq ?? '';
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

}
