<?php
/**
 * Contrôleur de gestion des genres de jeux
 * 
 * Ce contrôleur gère toutes les opérations CRUD sur les genres :
 * - Affichage de la liste des genres
 * - Ajout d'un nouveau genre
 * - Modification d'un genre existant
 * - Suppression d'un genre
 * 
 * @package App\Controller
 * @author Original
 * @version 1.0
 */

namespace App\Controller;

// Imports nécessaires
use Symfony\Component\HttpFoundation\Response;
require_once 'modele/class.PdoJeux.inc.php';
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use PdoJeux;

class GenresController extends AbstractController
{
    /**
     * Méthode privée pour afficher la liste des genres
     * 
     * Cette méthode centralise l'affichage de la liste des genres
     * et est utilisée par toutes les actions du contrôleur
     * 
     * @param PdoJeux $db Instance de la connexion à la base de données
     * @param int $idGenreModif ID du genre en cours de modification (-1 si aucun)
     * @param int $idGenreNotif ID du genre concerné par une notification (-1 si aucun)
     * @param string $notification Type de notification à afficher ('rien', 'Ajouté', 'Modifié', 'Supprimé')
     * @return Response Vue Twig avec les données nécessaires
     */
    private function afficherGenres(PdoJeux $db, int $idGenreModif, int $idGenreNotif, string $notification)
    {
        // Récupération des données nécessaires
        $tbMembres = $db->getLesMembres();
        $tbGenres = $db->getLesGenresComplet();

        // Rendu de la vue avec toutes les données
        return $this->render('lesGenres.html.twig', array(
            'menuActif' => 'Jeux',
            'tbGenres' => $tbGenres,
            'tbMembres' => $tbMembres,
            'idGenreModif' => $idGenreModif,
            'idGenreNotif' => $idGenreNotif,
            'notification' => $notification
        ));
    }

    /**
     * Affiche la liste des genres
     * 
     * @Route("/genres", name="genres_afficher")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @return Response Liste des genres ou page de connexion
     */
    #[Route('/genres', name: 'genres_afficher')]
    public function index(SessionInterface $session)
    {
        // Vérification de l'authentification
        if ($session->has('idUtilisateur')) {
            $db = PdoJeux::getPdoJeux();
            return $this->afficherGenres($db, -1, -1, 'rien');
        } else {
            return $this->render('connexion.html.twig');
        }
    }

    /**
     * Ajoute un nouveau genre
     * 
     * @Route("/genres/ajouter", name="genres_ajouter")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue mise à jour avec le nouveau genre
     */
    #[Route('/genres/ajouter', name: 'genres_ajouter')]
    public function ajouter(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $idGenreNotif = -1;
        $notification = 'rien';

        // Vérification et traitement du formulaire
        if (!empty($request->request->get('txtLibGenre'))) {
            $idGenreNotif = $db->ajouterGenre($request->request->get('txtLibGenre'));
            $notification = 'Ajouté';
        }

        return $this->afficherGenres($db, -1, $idGenreNotif, $notification);
    }

    /**
     * Prépare la modification d'un genre
     * 
     * @Route("/genres/demandermodifier", name="genres_demandermodifier")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue avec le formulaire de modification
     */
    #[Route('/genres/demandermodifier', name: 'genres_demandermodifier')]
    public function demanderModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        return $this->afficherGenres(
            $db,
            (int)$request->request->get('txtIdGenre'),
            -1,
            'rien'
        );
    }

    /**
     * Valide la modification d'un genre
     * 
     * @Route("/genres/validermodifier", name="genres_validermodifier")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue mise à jour avec les modifications
     */
    #[Route('/genres/validermodifier', name: 'genres_validermodifier')]
    public function validerModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $idGenre = (int)$request->request->get('txtIdGenre');
        
        // Mise à jour du genre dans la base de données
        $db->modifierGenre(
            $idGenre,
            (string)$request->request->get('txtLibGenre')
        );

        return $this->afficherGenres($db, -1, $idGenre, 'Modifié');
    }

    /**
     * Supprime un genre
     * 
     * @Route("/genres/supprimer", name="genres_supprimer")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue mise à jour sans le genre supprimé
     */
    #[Route('/genres/supprimer', name: 'genres_supprimer')]
    public function supprimer(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        
        // Suppression du genre
        $db->supprimerGenre((int)$request->request->get('txtIdGenre'));
        
        // Message de confirmation
        $this->addFlash('success', 'Le genre a été supprimé');
        
        return $this->afficherGenres($db, -1, -1, 'rien');
    }
}

