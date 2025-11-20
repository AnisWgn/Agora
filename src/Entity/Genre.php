<?php

namespace App\Entity;

use App\Repository\GenreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GenreRepository::class)]
class Genre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libGenre = null;

    /**
     * @var Collection<int, JeuVideo>
     */
    #[ORM\OneToMany(targetEntity: JeuVideo::class, mappedBy: 'genre')]
    private Collection $jeuVideos;

    public function __construct()
    {
        $this->jeuVideos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibGenre(): ?string
    {
        return $this->libGenre;
    }

    public function setLibGenre(string $libGenre): static
    {
        $this->libGenre = $libGenre;

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
            $jeuVideo->setGenre($this);
        }

        return $this;
    }

    public function removeJeuVideo(JeuVideo $jeuVideo): static
    {
        if ($this->jeuVideos->removeElement($jeuVideo)) {
            // set the owning side to null (unless already changed)
            if ($jeuVideo->getGenre() === $this) {
                $jeuVideo->setGenre(null);
            }
        }

        return $this;
    }
}
