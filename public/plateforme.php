<!-- <?php

// #[ORM\Entity]
// class Plateforme {
//  #[ORM\Id]
//  #[ORM\GeneratedValue]
//  #[ORM\Column]
//  private int $id;
//  #[ORM\OneToMany(mappedBy: 'plateforme', targetEntity: Jeux::class)]
//  private Collection $jeux;
// }
?> -->
<?php

#[ORM\Entity]
class Plateforme {
 #[ORM\Id]
 #[ORM\GeneratedValue]
 #[ORM\Column]
 private int $id;
 #[ORM\ManyToMany(mappedBy: 'plateforme', targetEntity: Jeux::class)]
 private Collection $jeux;
}
?>