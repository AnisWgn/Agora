<?php
/**
 * Contrôleur de gestion des jeux vidéo
 * 
 * Ce contrôleur gère toutes les opérations CRUD sur les jeux :
 * - Affichage de la liste des jeux
 * - Ajout d'un nouveau jeu
 * - Modification d'un jeu existant
 * - Suppression d'un jeu
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

class JeuxController extends AbstractController
{
    /**
     * Méthode privée pour afficher la liste des jeux
     * 
     * Cette méthode centralise l'affichage de la liste des jeux
     * et est utilisée par toutes les actions du contrôleur
     * 
     * @param PdoJeux $db Instance de la connexion à la base de données
     * @param string|null $refJeuModif Référence du jeu en cours de modification (null si aucun)
     * @param string|null $refJeuNotif Référence du jeu concerné par une notification (null si aucun)
     * @param string $notification Type de notification à afficher ('rien', 'Ajouté', 'Modifié', 'Supprimé')
     * @param SessionInterface|null $session Session Symfony
     * @return Response Vue Twig avec les données nécessaires
     */
    private function afficherJeux(PdoJeux $db, ?string $refJeuModif, ?string $refJeuNotif, string $notification, ?SessionInterface $session = null)
    {
        // Récupération des données nécessaires
        $tbJeux = $db->getLesJeux();
        $tbGenres = $db->getLesGenres();
        $tbMarques = $db->getLesMarques();
        $tbPlateformes = $db->getLesPlateformes();
        $tbPegis = $db->getLesPegis();

        // Préparation des données de session pour le template
        $sessionData = [];
        if ($session && $session->has('idUtilisateur')) {
            $sessionData = [
                'idUtilisateur' => $session->get('idUtilisateur'),
                'nomUtilisateur' => $session->get('nomUtilisateur'),
                'prenomUtilisateur' => $session->get('prenomUtilisateur'),
            ];
        } elseif (isset($_SESSION['idUtilisateur'])) {
            $sessionData = [
                'idUtilisateur' => $_SESSION['idUtilisateur'],
                'nomUtilisateur' => $_SESSION['nomUtilisateur'] ?? '',
                'prenomUtilisateur' => $_SESSION['prenomUtilisateur'] ?? '',
            ];
        }

        // Rendu de la vue avec toutes les données
        return $this->render('lesJeux.html.twig', array(
            'menuActif' => 'Jeux',
            'tbJeux' => $tbJeux,
            'tbGenres' => $tbGenres,
            'tbMarques' => $tbMarques,
            'tbPlateformes' => $tbPlateformes,
            'tbPegis' => $tbPegis,
            'notification' => $notification,
            'refJeuModif' => $refJeuModif,
            'refJeuNotif' => $refJeuNotif,
            'session' => $sessionData
        ));
    }

    /**
     * Affiche la liste des jeux
     * 
     * @Route("/jeux", name="jeux_afficher")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @return Response Liste des jeux ou page de connexion
     */
    #[Route('/jeux', name: 'jeux_afficher')]
    public function index(SessionInterface $session)
    {
        // Vérification de l'authentification (utilise la session PHP native ou Symfony)
        if (isset($_SESSION['idUtilisateur']) || $session->has('idUtilisateur')) {
            $db = PdoJeux::getPdoJeux();
            return $this->afficherJeux($db, null, null, 'rien', $session);
        } else {
            return $this->render('connexion.html.twig');
        }
    }

    /**
     * Ajoute un nouveau jeu
     * 
     * @Route("/jeux/ajouter", name="jeux_ajouter")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue mise à jour avec le nouveau jeu
     */
    #[Route('/jeux/ajouter', name: 'jeux_ajouter')]
    public function ajouter(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $refJeuNotif = null;
        $notification = 'rien';

        // Vérification et traitement du formulaire
        $refJeu = $request->request->get('refJeu') ?? '';
        $nom = $request->request->get('nom') ?? '';
        $dateParution = $request->request->get('dateParution') ?? '';
        
        if (!empty($refJeu) && !empty($nom) && !empty($dateParution)) {
            $prix = (float)($request->request->get('prix') ?? 0);
            $idGenre = (int)($request->request->get('idGenre') ?? 0);
            $idMarque = (int)($request->request->get('idMarque') ?? 0);
            $idPlateforme = (int)($request->request->get('idPlateforme') ?? 0);
            $idPegi = (int)($request->request->get('idPegi') ?? 0);
            
            $db->ajouterJeu($refJeu, $nom, $prix, $dateParution, $idGenre, $idMarque, $idPlateforme, $idPegi);
            $refJeuNotif = $refJeu;
            $notification = 'Ajouté';
        }

        return $this->afficherJeux($db, null, $refJeuNotif, $notification, $session);
    }

    /**
     * Prépare la modification d'un jeu
     * 
     * @Route("/jeux/demandermodifier", name="jeux_demandermodifier")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue avec le formulaire de modification
     */
    #[Route('/jeux/demandermodifier', name: 'jeux_demandermodifier')]
    public function demanderModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $refJeuModif = $request->request->get('refJeu') ?? null;
        return $this->afficherJeux($db, $refJeuModif, null, 'rien', $session);
    }

    /**
     * Valide la modification d'un jeu
     * 
     * @Route("/jeux/validermodifier", name="jeux_validermodifier")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue mise à jour avec les modifications
     */
    #[Route('/jeux/validermodifier', name: 'jeux_validermodifier')]
    public function validerModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $refJeu = $request->request->get('refJeu') ?? '';
        
        if (!empty($refJeu)) {
            $nom = $request->request->get('nom') ?? '';
            $prix = (float)($request->request->get('prix') ?? 0);
            $dateParution = $request->request->get('dateParution') ?? '';
            $idGenre = (int)($request->request->get('idGenre') ?? 0);
            $idMarque = (int)($request->request->get('idMarque') ?? 0);
            $idPlateforme = (int)($request->request->get('idPlateforme') ?? 0);
            $idPegi = (int)($request->request->get('idPegi') ?? 0);
            
            // Mise à jour du jeu dans la base de données
            $db->modifierJeu($refJeu, $nom, $prix, $dateParution, $idGenre, $idMarque, $idPlateforme, $idPegi);
        }

        return $this->afficherJeux($db, null, $refJeu, 'Modifié', $session);
    }

    /**
     * Supprime un jeu
     * 
     * @Route("/jeux/supprimer", name="jeux_supprimer")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue mise à jour sans le jeu supprimé
     */
    #[Route('/jeux/supprimer', name: 'jeux_supprimer')]
    public function supprimer(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $refJeu = $request->request->get('refJeu') ?? '';
        
        if (!empty($refJeu)) {
            // Suppression du jeu
            $db->supprimerJeu($refJeu);
            
            // Message de confirmation
            $this->addFlash('success', 'Le jeu a été supprimé');
        }
        
        return $this->afficherJeux($db, null, $refJeu, 'Supprimé', $session);
    }
}

