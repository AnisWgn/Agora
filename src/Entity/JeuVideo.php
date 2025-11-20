<?php

namespace App\Entity;

use App\Repository\JeuVideoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JeuVideoRepository::class)]
class JeuVideo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $refJeu = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?float $PRIX = null;

    #[ORM\Column]
    private ?\DateTime $dateParution = null;

    #[ORM\ManyToOne(inversedBy: 'jeuVideos')]
    private ?Plateforme $plateforme = null;

    #[ORM\ManyToOne(inversedBy: 'jeuVideos')]
    private ?Pegi $pegi = null;

    #[ORM\ManyToOne(inversedBy: 'jeuVideos')]
    private ?Genre $genre = null;

    #[ORM\ManyToOne(inversedBy: 'jeuVideos')]
    private ?Marque $marque = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRefJeu(): ?string
    {
        return $this->refJeu;
    }

    public function setRefJeu(string $refJeu): static
    {
        $this->refJeu = $refJeu;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPRIX(): ?float
    {
        return $this->PRIX;
    }

    public function setPRIX(float $PRIX): static
    {
        $this->PRIX = $PRIX;

        return $this;
    }

    public function getDateParution(): ?\DateTime
    {
        return $this->dateParution;
    }

    public function setDateParution(\DateTime $dateParution): static
    {
        $this->dateParution = $dateParution;

        return $this;
    }

    public function getPlateforme(): ?Plateforme
    {
        return $this->plateforme;
    }

    public function setPlateforme(?Plateforme $plateforme): static
    {
        $this->plateforme = $plateforme;

        return $this;
    }

    public function getPegi(): ?Pegi
    {
        return $this->pegi;
    }

    public function setPegi(?Pegi $pegi): static
    {
        $this->pegi = $pegi;

        return $this;
    }

    public function getGenre(): ?Genre
    {
        return $this->genre;
    }

    public function setGenre(?Genre $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getMarque(): ?Marque
    {
        return $this->marque;
    }

    public function setMarque(?Marque $marque): static
    {
        $this->marque = $marque;

        return $this;
    }
}
