 <?php
 class Jeux {
 #[ORM\Id]
 #[ORM\GeneratedValue]
 #[ORM\Column]
 private int $id;
 #[ORM\ManyToOne(targetEntity: Plateforme::class, inversedBy: 'jeux')]
 private ?Plateforme $plateforme = null;
}

?>