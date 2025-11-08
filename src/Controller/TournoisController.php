<?php
/**
 * Contrôleur de gestion des tournois
 * 
 * Ce contrôleur gère :
 * - L'affichage de la page de gestion des tournois
 * - La vérification de l'authentification de l'utilisateur
 * - La préparation des données de session pour le template
 * 
 * @package App\Controller
 * @author Original
 * @version 1.0
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Contrôleur final pour la gestion des tournois
 * 
 * Cette classe ne peut pas être étendue (final) pour garantir
 * l'intégrité du comportement de gestion des tournois
 */
final class TournoisController extends AbstractController
{
    /**
     * Affichage de la page de gestion des tournois
     * 
     * Cette méthode :
     * - Vérifie que l'utilisateur est authentifié
     * - Récupère les données de session (Symfony ou PHP native)
     * - Affiche la page de gestion des tournois si connecté
     * - Redirige vers la page de connexion si non connecté
     * 
     * @Route("/tournois", name="tournois_afficher")
     * @param SessionInterface $session Interface de gestion de session Symfony
     * @return Response Page de gestion des tournois ou page de connexion
     */
    #[Route('/tournois', name: 'tournois_afficher')]
    public function index(SessionInterface $session): Response
    {
        // ====================================
        // ===== VÉRIFICATION AUTHENTIFICATION =====
        // ====================================
        
        // Vérification de l'authentification via session PHP native ou Symfony
        // Compatibilité avec les deux systèmes de session
        $isAuthenticated = isset($_SESSION['idUtilisateur']) || $session->has('idUtilisateur');
        
        if ($isAuthenticated) {
            // ====================================
            // ===== PRÉPARATION DES DONNÉES DE SESSION =====
            // ====================================
            
            // Initialisation du tableau de données de session
            $sessionData = [];
            
            // Priorité à la session Symfony si disponible
            if ($session && $session->has('idUtilisateur')) {
                $sessionData = [
                    'idUtilisateur' => $session->get('idUtilisateur'),
                    'nomUtilisateur' => $session->get('nomUtilisateur'),
                    'prenomUtilisateur' => $session->get('prenomUtilisateur'),
                ];
            } 
            // Fallback vers la session PHP native si Symfony n'est pas disponible
            elseif (isset($_SESSION['idUtilisateur'])) {
                $sessionData = [
                    'idUtilisateur' => $_SESSION['idUtilisateur'],
                    'nomUtilisateur' => $_SESSION['nomUtilisateur'] ?? '',
                    'prenomUtilisateur' => $_SESSION['prenomUtilisateur'] ?? '',
                ];
            }
            
            // ====================================
            // ===== RENDU DE LA PAGE DE GESTION =====
            // ====================================
            
            return $this->render('tournois/index.html.twig', [
                'controller_name' => 'TournoisController',
                'menuActif' => 'Jeux',
                'session' => $sessionData
            ]);
        } 
        else {
            // ====================================
            // ===== REDIRECTION VERS CONNEXION =====
            // ====================================
            
            // Utilisateur non authentifié : affichage de la page de connexion
            return $this->render('connexion.html.twig');
        }
    }
}
