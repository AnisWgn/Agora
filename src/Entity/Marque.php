<?php

namespace App\Entity;

use App\Repository\MarqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MarqueRepository::class)]
class Marque
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $libMarque = null;

    /**
     * @var Collection<int, JeuVideo>
     */
    #[ORM\OneToMany(targetEntity: JeuVideo::class, mappedBy: 'marque')]
    private Collection $jeuVideos;

    public function __construct()
    {
        $this->jeuVideos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibMarque(): ?string
    {
        return $this->libMarque;
    }

    public function setLibMarque(string $libMarque): static
    {
        $this->libMarque = $libMarque;

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
            $jeuVideo->setMarque($this);
        }

        return $this;
    }

    public function removeJeuVideo(JeuVideo $jeuVideo): static
    {
        if ($this->jeuVideos->removeElement($jeuVideo)) {
            // set the owning side to null (unless already changed)
            if ($jeuVideo->getMarque() === $this) {
                $jeuVideo->setMarque(null);
            }
        }

        return $this;
    }
}
