<?php

namespace App\Entity;

use App\Repository\GenreRepository;
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
}
