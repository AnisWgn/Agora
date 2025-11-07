<?php
/**
 * Contrôleur de gestion des PEGI (classifications de jeux)
 * 
 * Ce contrôleur gère toutes les opérations CRUD sur les PEGI :
 * - Affichage de la liste des PEGI
 * - Ajout d'un nouveau PEGI
 * - Modification d'un PEGI existant
 * - Suppression d'un PEGI
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

class PegisController extends AbstractController
{
    /**
     * Méthode privée pour afficher la liste des PEGI
     * 
     * Cette méthode centralise l'affichage de la liste des PEGI
     * et est utilisée par toutes les actions du contrôleur
     * 
     * @param PdoJeux $db Instance de la connexion à la base de données
     * @param int $idPegiModif ID du PEGI en cours de modification (-1 si aucun)
     * @param int $idPegiNotif ID du PEGI concerné par une notification (-1 si aucun)
     * @param string $notification Type de notification à afficher ('rien', 'Ajouté', 'Modifié', 'Supprimé')
     * @param SessionInterface|null $session Session Symfony
     * @return Response Vue Twig avec les données nécessaires
     */
    private function afficherPegis(PdoJeux $db, int $idPegiModif, int $idPegiNotif, string $notification, ?SessionInterface $session = null)
    {
        // Récupération des données nécessaires
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
        return $this->render('lesPegis.html.twig', array(
            'menuActif' => 'Jeux',
            'tbPegis' => $tbPegis,
            'idPegiModif' => $idPegiModif,
            'idPegiNotif' => $idPegiNotif,
            'notification' => $notification,
            'session' => $sessionData
        ));
    }

    /**
     * Affiche la liste des PEGI
     * 
     * @Route("/pegis", name="pegis_afficher")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @return Response Liste des PEGI ou page de connexion
     */
    #[Route('/pegis', name: 'pegis_afficher')]
    public function index(SessionInterface $session)
    {
        // Vérification de l'authentification (utilise la session PHP native ou Symfony)
        if (isset($_SESSION['idUtilisateur']) || $session->has('idUtilisateur')) {
            $db = PdoJeux::getPdoJeux();
            return $this->afficherPegis($db, -1, -1, 'rien', $session);
        } else {
            return $this->render('connexion.html.twig');
        }
    }

    /**
     * Ajoute un nouveau PEGI
     * 
     * @Route("/pegis/ajouter", name="pegis_ajouter")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue mise à jour avec le nouveau PEGI
     */
    #[Route('/pegis/ajouter', name: 'pegis_ajouter')]
    public function ajouter(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $idPegiNotif = -1;
        $notification = 'rien';

        // Vérification et traitement du formulaire
        if (!empty($request->request->get('txtAge')) && !empty($request->request->get('txtDescription'))) {
            $age = $request->request->get('txtAge');
            $description = $request->request->get('txtDescription');
            $idPegiNotif = $db->ajouterPegi($age, $description);
            $notification = 'Ajouté';
        }

        return $this->afficherPegis($db, -1, $idPegiNotif, $notification, $session);
    }

    /**
     * Prépare la modification d'un PEGI
     * 
     * @Route("/pegis/demandermodifier", name="pegis_demandermodifier")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue avec le formulaire de modification
     */
    #[Route('/pegis/demandermodifier', name: 'pegis_demandermodifier')]
    public function demanderModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        return $this->afficherPegis(
            $db,
            (int)$request->request->get('txtIdPegi'),
            -1,
            'rien',
            $session
        );
    }

    /**
     * Valide la modification d'un PEGI
     * 
     * @Route("/pegis/validermodifier", name="pegis_validermodifier")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue mise à jour avec les modifications
     */
    #[Route('/pegis/validermodifier', name: 'pegis_validermodifier')]
    public function validerModifier(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        $idPegi = (int)$request->request->get('txtIdPegi');
        
        // Mise à jour du PEGI dans la base de données
        $age = (string)$request->request->get('txtAge');
        $description = (string)$request->request->get('txtDescription');
        $db->modifierPegi($idPegi, $age, $description);

        return $this->afficherPegis($db, -1, $idPegi, 'Modifié', $session);
    }

    /**
     * Supprime un PEGI
     * 
     * @Route("/pegis/supprimer", name="pegis_supprimer")
     * @param SessionInterface $session Pour vérifier l'authentification
     * @param Request $request Données du formulaire
     * @return Response Vue mise à jour sans le PEGI supprimé
     */
    #[Route('/pegis/supprimer', name: 'pegis_supprimer')]
    public function supprimer(SessionInterface $session, Request $request)
    {
        $db = PdoJeux::getPdoJeux();
        
        // Suppression du PEGI
        $db->supprimerPegis((int)$request->request->get('txtIdPegi'));
        
        // Message de confirmation
        $this->addFlash('success', 'Le PEGI a été supprimé');
        
        return $this->afficherPegis($db, -1, -1, 'rien', $session);
    }
}

