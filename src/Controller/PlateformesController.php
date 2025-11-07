<?php
/**
 * Contrôleur de gestion des plateformes de jeux
 * 
 * Ce contrôleur gère toutes les opérations CRUD sur les plateformes :
 * - Affichage de la liste des plateformes
 * - Ajout d'une nouvelle plateforme
 * - Modification d'une plateforme existante
 * - Suppression d'une plateforme
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

class PlateformesController extends AbstractController
{
    /**
     * Méthode privée pour afficher la liste des plateformes
     * 
     * Cette méthode centralise l'affichage de la liste des plateformes
     * et est utilisée par toutes les actions du contrôleur
     * 
     * @param PdoJeux $db Instance de la connexion à la base de données
     * @param int $idPlateformeModif ID de la plateforme en cours de modification (-1 si aucun)
     * @param int $idPlateformeNotif ID de la plateforme concernée par une notification (-1 si aucun)
     * @param string $notification Type de notification à afficher ('rien', 'Ajouté', 'Modifié', 'Supprimé')
     * @param SessionInterface|null $session Session Symfony
     * @return Response Vue Twig avec les données nécessaires
     */
    private function afficherPlateformes(PdoJeux $db, int $idPlateformeModif, int $idPlateformeNotif, string $notification, ?SessionInterface $session = null)
    {
        // Récupération des données nécessaires
        $tbPlateformes = $db->getLesPlateformes();

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
        return $this->render('lesPlateformes.html.twig', array(
            'menuActif' => 'Jeux',
            'tbPlateformes' => $tbPlateformes,
            'idPlateformeModif' => $idPlateformeModif,
            'idPlateformeNotif' => $idPlateformeNotif,
            'notification' => $notification,
            'session' => $sessionData
        ));
    }

    /**
     * Affiche la liste des plateformes
     * 
     * @Route("/plateformes", name="plateformes_afficher")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @return Response Liste des plateformes ou page de connexion
     */
    #[Route('/plateformes', name: 'plateformes_afficher')]
    public function index(SessionInterface $session)
    {
        // Vérification de l'authentification (utilise la session PHP native ou Symfony)
        if (isset($_SESSION['idUtilisateur']) || $session->has('idUtilisateur')) {
            $db = PdoJeux::getPdoJeux();
            return $this->afficherPlateformes($db, -1, -1, 'rien', $session);
        } else {
            return $this->render('connexion.html.twig');
        }
    }

    /**
     * Ajoute une nouvelle plateforme
     * 
     * @Route("/plateformes/ajouter", name="plateformes_ajouter")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue mise à jour avec la nouvelle plateforme
     */
    #[Route('/plateformes/ajouter', name: 'plateformes_ajouter')]
    public function ajouter(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $idPlateformeNotif = -1;
        $notification = 'rien';

        // Vérification et traitement du formulaire
        if (!empty($request->request->get('txtLibPlateforme'))) {
            $libPlateforme = $request->request->get('txtLibPlateforme');
            $idPlateformeNotif = $db->ajouterPlateforme($libPlateforme);
            $notification = 'Ajouté';
        }

        return $this->afficherPlateformes($db, -1, $idPlateformeNotif, $notification, $session);
    }

    /**
     * Prépare la modification d'une plateforme
     * 
     * @Route("/plateformes/demandermodifier", name="plateformes_demandermodifier")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue avec le formulaire de modification
     */
    #[Route('/plateformes/demandermodifier', name: 'plateformes_demandermodifier')]
    public function demanderModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        return $this->afficherPlateformes(
            $db,
            (int)$request->request->get('txtIdPlateforme'),
            -1,
            'rien',
            $session
        );
    }

    /**
     * Valide la modification d'une plateforme
     * 
     * @Route("/plateformes/validermodifier", name="plateformes_validermodifier")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue mise à jour avec les modifications
     */
    #[Route('/plateformes/validermodifier', name: 'plateformes_validermodifier')]
    public function validerModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $idPlateforme = (int)$request->request->get('txtIdPlateforme');
        
        // Mise à jour de la plateforme dans la base de données
        $libPlateforme = (string)$request->request->get('txtLibPlateforme');
        $db->modifierPlateforme($idPlateforme, $libPlateforme);

        return $this->afficherPlateformes($db, -1, $idPlateforme, 'Modifié', $session);
    }

    /**
     * Supprime une plateforme
     * 
     * @Route("/plateformes/supprimer", name="plateformes_supprimer")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue mise à jour sans la plateforme supprimée
     */
    #[Route('/plateformes/supprimer', name: 'plateformes_supprimer')]
    public function supprimer(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        
        // Suppression de la plateforme
        $db->supprimerPlateforme((int)$request->request->get('txtIdPlateforme'));
        
        // Message de confirmation
        $this->addFlash('success', 'La plateforme a été supprimée');
        
        return $this->afficherPlateformes($db, -1, -1, 'rien', $session);
    }
}

