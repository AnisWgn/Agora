<?php
/**
 * Contrôleur de gestion des marques de jeux
 * 
 * Ce contrôleur gère toutes les opérations CRUD sur les marques :
 * - Affichage de la liste des marques
 * - Ajout d'une nouvelle marque
 * - Modification d'une marque existante
 * - Suppression d'une marque
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

class MarquesController extends AbstractController
{
    /**
     * Méthode privée pour afficher la liste des marques
     * 
     * Cette méthode centralise l'affichage de la liste des marques
     * et est utilisée par toutes les actions du contrôleur
     * 
     * @param PdoJeux $db Instance de la connexion à la base de données
     * @param int $idMarqueModif ID de la marque en cours de modification (-1 si aucun)
     * @param int $idMarqueNotif ID de la marque concernée par une notification (-1 si aucun)
     * @param string $notification Type de notification à afficher ('rien', 'Ajouté', 'Modifié', 'Supprimé')
     * @param SessionInterface|null $session Session Symfony
     * @return Response Vue Twig avec les données nécessaires
     */
    private function afficherMarques(PdoJeux $db, int $idMarqueModif, int $idMarqueNotif, string $notification, ?SessionInterface $session = null)
    {
        // Récupération des données nécessaires
        $tbMarques = $db->getLesMarques();

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
        return $this->render('lesMarques.html.twig', array(
            'menuActif' => 'Jeux',
            'tbMarques' => $tbMarques,
            'idMarqueModif' => $idMarqueModif,
            'idMarqueNotif' => $idMarqueNotif,
            'notification' => $notification,
            'session' => $sessionData
        ));
    }

    /**
     * Affiche la liste des marques
     * 
     * @Route("/marques", name="marques_afficher")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @return Response Liste des marques ou page de connexion
     */
    #[Route('/marques', name: 'marques_afficher')]
    public function index(SessionInterface $session)
    {
        // Vérification de l'authentification (utilise la session PHP native ou Symfony)
        if (isset($_SESSION['idUtilisateur']) || $session->has('idUtilisateur')) {
            $db = PdoJeux::getPdoJeux();
            return $this->afficherMarques($db, -1, -1, 'rien', $session);
        } else {
            return $this->render('connexion.html.twig');
        }
    }

    /**
     * Ajoute une nouvelle marque
     * 
     * @Route("/marques/ajouter", name="marques_ajouter")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue mise à jour avec la nouvelle marque
     */
    #[Route('/marques/ajouter', name: 'marques_ajouter')]
    public function ajouter(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $idMarqueNotif = -1;
        $notification = 'rien';

        // Vérification et traitement du formulaire
        if (!empty($request->request->get('txtNomMarque'))) {
            $nomMarque = $request->request->get('txtNomMarque');
            $idMarqueNotif = $db->ajouterMarque($nomMarque);
            $notification = 'Ajouté';
        }

        return $this->afficherMarques($db, -1, $idMarqueNotif, $notification, $session);
    }

    /**
     * Prépare la modification d'une marque
     * 
     * @Route("/marques/demandermodifier", name="marques_demandermodifier")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue avec le formulaire de modification
     */
    #[Route('/marques/demandermodifier', name: 'marques_demandermodifier')]
    public function demanderModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        return $this->afficherMarques(
            $db,
            (int)$request->request->get('txtIdMarque'),
            -1,
            'rien',
            $session
        );
    }

    /**
     * Valide la modification d'une marque
     * 
     * @Route("/marques/validermodifier", name="marques_validermodifier")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue mise à jour avec les modifications
     */
    #[Route('/marques/validermodifier', name: 'marques_validermodifier')]
    public function validerModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $idMarque = (int)$request->request->get('txtIdMarque');
        
        // Mise à jour de la marque dans la base de données
        $nomMarque = (string)$request->request->get('txtNomMarque');
        $db->modifierMarque($idMarque, $nomMarque);

        return $this->afficherMarques($db, -1, $idMarque, 'Modifié', $session);
    }

    /**
     * Supprime une marque
     * 
     * @Route("/marques/supprimer", name="marques_supprimer")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue mise à jour sans la marque supprimée
     */
    #[Route('/marques/supprimer', name: 'marques_supprimer')]
    public function supprimer(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        
        // Suppression de la marque
        $db->supprimerMarque((int)$request->request->get('txtIdMarque'));
        
        // Message de confirmation
        $this->addFlash('success', 'La marque a été supprimée');
        
        return $this->afficherMarques($db, -1, -1, 'rien', $session);
    }
}

