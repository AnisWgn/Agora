<?php
/**
 * Point d'entrée unique de l'application AgoraBo
 * 
 * Ce fichier gère:
 * - L'initialisation de la session
 * - Le routage des requêtes vers Symfony ou les contrôleurs legacy
 * 
 * @author MD
 * @package default
 * @version 1.0
 */

// ====================================
// ===== INITIALISATION GÉNÉRALE =====
// ====================================

// Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration des erreurs (masquer les dépréciations temporairement)
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

// Chargement de l'autoloader Composer
require_once __DIR__ . '/../vendor/autoload.php';

// ====================================
// ===== ROUTAGE =====
// ====================================

// Si c'est une route legacy (avec paramètre uc), gérer le routage legacy
// Sinon, passer à Symfony (y compris pour /)
if (isset($_GET['uc'])) {
    // ====================================
    // ===== ROUTAGE LEGACY =====
    // ====================================
    
    // Chargement des variables d'environnement pour les routes legacy
    if (file_exists(__DIR__ . '/../.env')) {
        try {
            (new \Symfony\Component\Dotenv\Dotenv())->bootEnv(__DIR__ . '/../.env');
        } catch (Throwable $e) {
            // En cas d'erreur, on continue
        }
    }
    
    // Récupération de la route demandée (use case)
    $uc = $_GET['uc'];
    
    // Routage vers le contrôleur approprié
    switch ($uc) {
        case 'index':
            header('Location: /');
            exit;
            
        case 'gererGenres':
            header('Location: /genres');
            exit;
            
        case 'gererPlateformes':
            header('Location: /plateformes');
            exit;
            
        case 'gererMarques':
            header('Location: /marques');
            exit;
            
        case 'gererPegis':
            header('Location: /pegis');
            exit;
            
        case 'gererJeux':
            header('Location: /jeux');
            exit;
            
        case 'deconnexion':
            header('Location: /deconnexion');
            exit;
            
        default:
            // Pour les autres routes legacy, rediriger vers Symfony
            header('Location: /');
            exit;
    }
}

// ====================================
// ===== ROUTAGE SYMFONY =====
// ====================================

// Toutes les autres routes (y compris /) sont gérées par Symfony
// Chargement des variables d'environnement
if (file_exists(__DIR__ . '/../.env')) {
    (new \Symfony\Component\Dotenv\Dotenv())->bootEnv(__DIR__ . '/../.env');
}

// Création du Kernel Symfony
$kernel = new \App\Kernel($_ENV['APP_ENV'] ?? 'dev', (bool)($_ENV['APP_DEBUG'] ?? true));

// Création de la requête Symfony
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

// Traitement de la requête
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
exit;
?>
