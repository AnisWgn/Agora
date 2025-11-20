<?php

namespace App\Entity;

use App\Repository\PegiRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PegiRepository::class)]
class Pegi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $ageLimite = null;

    #[ORM\Column(length: 100)]
    private ?string $descPegi = null;

    /**
     * @var Collection<int, JeuVideo>
     */
    #[ORM\OneToMany(targetEntity: JeuVideo::class, mappedBy: 'pegi')]
    private Collection $jeuVideos;

    public function __construct()
    {
        $this->jeuVideos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgeLimite(): ?int
    {
        return $this->ageLimite;
    }

    public function setAgeLimite(int $ageLimite): static
    {
        $this->ageLimite = $ageLimite;

        return $this;
    }

    public function getDescPegi(): ?string
    {
        return $this->descPegi;
    }

    public function setDescPegi(string $descPegi): static
    {
        $this->descPegi = $descPegi;

        return $this;
    }

    /**
     * @return Collection<int, JeuVideo>
     */
    public function getJeuVideos(): Collection
    {
        return $this->jeuVideos;
    }

    public function addJeuVideo(JeuVideo $jeuVideo): static
    {
        if (!$this->jeuVideos->contains($jeuVideo)) {
            $this->jeuVideos->add($jeuVideo);
            $jeuVideo->setPegi($this);
        }

        return $this;
    }

    public function removeJeuVideo(JeuVideo $jeuVideo): static
    {
        if ($this->jeuVideos->removeElement($jeuVideo)) {
            // set the owning side to null (unless already changed)
            if ($jeuVideo->getPegi() === $this) {
                $jeuVideo->setPegi(null);
            }
        }

        return $this;
    }
}
