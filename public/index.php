<?php
/**
 * Page d'accueil de l'application AgoraBo
 * Point d'entrée unique de l'application gérant les jeux vidéo
 * 
 * Ce fichier gère:
 * - L'initialisation de la session
 * - La configuration de Twig
 * - Le routage des requêtes vers les différents contrôleurs
 * - La gestion des genres, plateformes, marques, pegis et jeux
 * 
 * @author MD
 * @package default
 * @version 1.0
 */

// ====================================
// ===== INITIALISATION GÉNÉRALE =====
// ====================================

// Démarrage de la session - IMPORTANT: Doit être fait avant tout affichage HTML
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration des erreurs (masquer les dépréciations temporairement)
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

// ====================================
// ===== CHARGEMENT DES DÉPENDANCES =====
// ====================================

// Inclusion de l'en-tête HTML et des styles
require __DIR__ . '/../templates/v_headerTwig.html';

// Chargement de la classe principale de gestion des jeux
require_once __DIR__ . '/../src/Controller/modele/class.PdoJeux.inc.php';

// Chargement de l'autoloader Composer
// Permet le chargement automatique de toutes les dépendances du projet
require_once __DIR__ . '/../vendor/autoload.php';

// ====================================
// ===== VÉRIFICATION ROUTES SYMFONY =====
// ====================================

// Détection des routes Symfony (commençant par /)
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$pathInfo = parse_url($requestUri, PHP_URL_PATH);

// Si c'est une route Symfony (commence par / et n'est pas index.php)
if ($pathInfo && $pathInfo !== '/' && $pathInfo !== '/index.php' && !isset($_GET['uc'])) {
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
}

// ====================================
// ===== CONFIGURATION ENVIRONNEMENT =====
// ====================================

// Chargement des variables d'environnement depuis le fichier .env
if (file_exists(__DIR__ . '/../.env')) {
    try {
        // Utilisation du composant Dotenv de Symfony pour charger les variables
        (new \Symfony\Component\Dotenv\Dotenv())->bootEnv(__DIR__ . '/../.env');
    } catch (Throwable $e) {
        // En cas d'erreur, on continue - PdoJeux signalera les variables manquantes
    }
}

// ====================================
// ===== CONFIGURATION DE TWIG =====
// ====================================

// Création du chargeur de templates Twig
// Pointe vers le dossier 'templates' du projet
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');

// Initialisation de l'environnement Twig
// En développement : pas de cache et mode debug activé
$twig = new \Twig\Environment($loader, array(
    'cache' => (defined('TWIG_CACHE') ? TWIG_CACHE : false),
    'debug' => (defined('TWIG_DEBUG') ? TWIG_DEBUG : false),
));
// ====================================
// ===== CONFIGURATION DES FONCTIONS TWIG =====
// ====================================

// Rendre la session disponible dans tous les templates Twig
$twig->addGlobal('session', $_SESSION);

// Fonction asset() pour gérer les chemins des ressources statiques
// Utilisée dans les templates pour les liens vers CSS, JS, images, etc.
$twig->addFunction(new \Twig\TwigFunction('asset', function (string $path) {
    // Si l'URL est absolue, la retourner telle quelle
    if (preg_match('#^(https?:)?//#', $path)) {
        return $path;
    }
    // Assurer que le chemin commence par un slash
    return '/' . ltrim($path, '/');
}));

// Fonction path() pour gérer les routes de l'application
// Permet d'avoir des URLs cohérentes dans toute l'application
$twig->addFunction(new \Twig\TwigFunction('path', function (string $routeName, array $params = []) {
    // Mapping des routes Symfony
    $symfonyRoutes = [
        'genres_afficher' => '/genres',
        'genres_ajouter' => '/genres/ajouter',
        'genres_demandermodifier' => '/genres/demandermodifier',
        'genres_validermodifier' => '/genres/validermodifier',
        'genres_supprimer' => '/genres/supprimer',
        'plateformes_afficher' => '/plateformes',
        'plateformes_ajouter' => '/plateformes/ajouter',
        'plateformes_demandermodifier' => '/plateformes/demandermodifier',
        'plateformes_validermodifier' => '/plateformes/validermodifier',
        'plateformes_supprimer' => '/plateformes/supprimer',
        'marques_afficher' => '/marques',
        'marques_ajouter' => '/marques/ajouter',
        'marques_demandermodifier' => '/marques/demandermodifier',
        'marques_validermodifier' => '/marques/validermodifier',
        'marques_supprimer' => '/marques/supprimer',
        'pegis_afficher' => '/pegis',
        'pegis_ajouter' => '/pegis/ajouter',
        'pegis_demandermodifier' => '/pegis/demandermodifier',
        'pegis_validermodifier' => '/pegis/validermodifier',
        'pegis_supprimer' => '/pegis/supprimer',
        'jeux_afficher' => '/jeux',
        'jeux_ajouter' => '/jeux/ajouter',
        'jeux_demandermodifier' => '/jeux/demandermodifier',
        'jeux_validermodifier' => '/jeux/validermodifier',
        'jeux_supprimer' => '/jeux/supprimer',
        'tournois_afficher' => '/tournois',
        'accueil' => '/',
        'deconnexion' => '/deconnexion',
    ];
    
    // Si c'est une route Symfony, retourner l'URL Symfony
    if (isset($symfonyRoutes[$routeName])) {
        $url = $symfonyRoutes[$routeName];
        // Ajouter les paramètres de requête si nécessaire
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }
    
    return $legacyMap[$routeName] ?? 'index.php';
}));
// ====================================
// ===== CONNEXION BASE DE DONNÉES =====
// ====================================

// Établissement de la connexion à la base de données
$db = PdoJeux::getPdoJeux();

// ====================================
// ===== GESTION AUTHENTIFICATION =====
// ====================================

// Vérification si l'utilisateur est connecté
// La variable de session 'idUtilisateur' est créée lors de la connexion réussie
if (!isset($_SESSION['idUtilisateur'])) {
    // Traitement POST local de la connexion (fallback si c_connexion.php absent)
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' &&
        (($_GET['uc'] ?? '') === 'connexion') && (($_GET['action'] ?? '') === 'validerConnexion')) {
        $login = $_POST['txtLogin'] ?? '';
        $mdpSaisi = $_POST['txtMdp'] ?? '';
        $mdpHacheClient = $_POST['hdMdp'] ?? '';
        $mdpPourVerif = $mdpHacheClient !== '' ? $mdpHacheClient : hash('sha512', $mdpSaisi);

        // Vérification BDD
        try {
            $utilisateur = $db->getUnMembre($login, $mdpPourVerif);
            if ($utilisateur) {
                $_SESSION['idUtilisateur'] = $utilisateur->idMembre;
                $_SESSION['nomUtilisateur'] = $utilisateur->nomMembre;
                $_SESSION['prenomUtilisateur'] = $utilisateur->prenomMembre;
                header('Location: index.php');
                exit;
            } else {
                echo $twig->render('connexion.html.twig', ['erreur' => 'Login ou mot de passe incorrect !']);
                exit;
            }
        } catch (Throwable $e) {
            echo $twig->render('connexion.html.twig', ['erreur' => 'Erreur BDD: ' . $e->getMessage()]);
            exit;
        }
    }
	$cPath = __DIR__ . '/controleur/c_connexion.php';
	if (file_exists($cPath)) {
		require $cPath;
	} else {
		// fallback: render the connexion template when procedural controller is absent
		echo $twig->render('connexion.html.twig');
	}
} else {
    // ====================================
    // ===== ROUTAGE DE L'APPLICATION =====
    // ====================================

    // Si aucune route n'est spécifiée, rediriger vers l'accueil
    if (!isset($_GET['uc'])) {
        $_GET['uc'] = 'index';
    }

    // Récupération de la route demandée (use case)
    $uc = $_GET['uc'];

    // Routage vers le contrôleur approprié
    switch ($uc) {
case 'index':
{
    // Affichage de la page d'accueil
    echo $twig->render('accueil.html.twig', [
        'menuActif' => 'Accueil'
    ]);
    break;
}
case 'gererGenres' :
{
    // Redirection vers la route Symfony
    header('Location: /genres');
    exit;
}
case 'gererPlateformes' :
{
    // Redirection vers la route Symfony
    header('Location: /plateformes');
    exit;
}
case 'gererMarques' :
{
    // Redirection vers la route Symfony
    header('Location: /marques');
    exit;
}
case 'gererPegis' :
{
    // Redirection vers la route Symfony
    header('Location: /pegis');
    exit;
}
case 'gererJeux' :
{
    // Redirection vers la route Symfony
    header('Location: /jeux');
    exit;
}
// ATTENTION, conserver les autres case dans votre code
case 'deconnexion' :
{
	$cDec = __DIR__ . '/controleur/c_deconnexion.php';
	if (file_exists($cDec)) {
		require $cDec;
	} else {
		// fallback: clear session and redirect to index
		session_unset();
		session_destroy();
		header('Location: ' . ($_SERVER['PHP_SELF'] ?? '/'));
		exit;
	}
	break;
}
}
}
// ====================================
// ===== NETTOYAGE ET FERMETURE =====
// ====================================

// Fermeture de la connexion à la base de données
$db = null;

// Note: Le pied de page est géré par Twig dans le template de base
?>
