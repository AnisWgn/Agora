<?php

namespace App\Controller;
use Doctrine\Persistence\ManagerRegistry;

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

    #[Route('/genre/{id}', name: 'app_genre_lire')]
    public function lire($id, ManagerRegistry $doctrine)
    {
        // ces 2 exemples retournent le entity manager par défaut
        // ici nous n'utilisons qu'une base de données donc le entity manager par défaut suffit
        $entityManager = $doctrine->getManager();
        // $entityManager = $doctrine->getManager('default');

        // {id} dans la route permet de récupérer $id en argument de la méthode
        // on utilise le Repository de la classe Genre
        // il s'agit d'une classe qui est utilisée pour les recherches d'entités (et donc de données dans la base)
        // la classe GenreRepository a été créée en même temps que l'entité par le make
        $genre = $entityManager
            ->getRepository(Genre::class)
            ->find($id);

        if (!$genre) {
            throw $this->createNotFoundException(
                'Ce genre n\'existe pas : ' . $id
            );
        }

        return new Response('Voici le libellé du genre : ' . $genre->getLibGenre());
    }

    #[Route('/genreautomatique/{id}', name: 'app_genreautomatique_lire')]
    public function lireautomatique(Genre $genre)
    {
        // grâce au Symfony\Bridge\Doctrine\ArgumentResolver\EntityValueResolver
        // il suffit de donner le genre en argument
        // la requête de recherche sera automatique
        // et une page 404 sera générée si le genre n'existe pas
        return new Response('Voici le libellé du genre lu automatiquement : ' . $genre->getLibGenre()) ;
        // on peut bien sûr également rendre un template
    }

    #[Route('/genre/modifier/{id}', name: 'app_genre_modifier')]
    public function modifier($id, EntityManagerInterface $entityManager)
    {
        // 1 recherche du genre
        $genre = $entityManager->getRepository(Genre::class)->find($id);
        // en cas de genre inexistant, affichage page 404
        if (!$genre) {
            throw $this->createNotFoundException(
                'Aucun genre avec l\'id ' . $id
            );
        }
        // 2 modification des propriétés
        $genre->setLibGenre('Action');
        // 3 exécution de l'update
        $entityManager->flush();
        // redirection vers l'affichage du genre
        return $this->redirectToRoute('app_genre_lire', [
            'id' => $genre->getId()
        ]);
    }

    #[Route('/genre/supprimer/{id}', name: 'app_genre_supprimer')]
    public function supprimer($id, EntityManagerInterface $entityManager)
    {
        // 1 recherche du genre
        $genre = $entityManager->getRepository(Genre::class)->find($id);
        // en cas de genre inexistant, affichage page 404
        if (!$genre) {
            throw $this->createNotFoundException(
                'Aucun genre avec l\'id ' . $id
            );
        }

        // 3 suppression du genre
        $entityManager->remove($genre);
        // 4 exécution de la suppression (DELETE)
        $entityManager->flush();      
        
        $libGenre = $genre->getLibGenre();
        return new Response('Le genre n° ' . $id . ' "' . $libGenre . '" a été supprimé.');
    }
}


