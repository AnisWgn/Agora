<?php

namespace App\Controller;

use App\Entity\Genre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GenreController extends AbstractController
{
    #[Route('/genre', name: 'app_genre')]
    public function index(): Response
    {
        return $this->render('genre/index.html.twig', [
            'controller_name' => 'GenreController',
            'menuActif' => 'Tournois',
        ]);
    }

    #[Route('/genre/creer', name: 'app_genre_creer')]
    public function creerGenre(EntityManagerInterface $entityManager): Response
    {
        // : Response type de retour de la méthode creerGenre
        // pour récupérer le EntityManager (manager d'entités, d'objets)
        // ajout de l'argument à la méthode comme ici creerGenre(EntityManagerInterface $entityManager)
        // ou on peut récupérer le EntityManager comme dans la méthode suivante

        // créer l'objet
        $genre = new Genre();
        $genre->setLibGenre('Aventure');

        // dire à Doctrine que l'objet sera (éventuellement) persisté
        $entityManager->persist($genre);

        // exécuter les requêtes (indiquées avec persist) ici il s'agit de l'ordre INSERT qui sera exécuté
        $entityManager->flush();

        return new Response('Nouveau genre enregistré, son id est : ' . $genre->getId());
    }
}

