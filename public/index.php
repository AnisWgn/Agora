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
$twig->addFunction(new \Twig\TwigFunction('path', function (string $routeName) {
    $map = [
        'accueil' => 'index.php',
        'deconnexion' => 'index.php?uc=deconnexion',
        'genres_afficher' => 'index.php?uc=gererGenres&action=afficherGenres',
        // Ajouter d'autres routes ici si nécessaire
    ];
    return $map[$routeName] ?? ('index.php');
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
case 'index' :
{
//$menuActif = '';
//require 'vue/v_menu.php';
//require 'vue/v_accueil.php';
echo $twig->render('accueil.html.twig');
break;
}
case 'gererGenres' :
{
	$cGerer = __DIR__ . '/controleur/c_gererGenres.php';
	if (file_exists($cGerer)) {
		require $cGerer;
	} else {
		// Charger les données depuis la base
		try {
			$tbMembres = $db->getLesMembres();
			$tbGenres = $db->getLesGenresComplet();
			// Initialiser les variables nécessaires
			$idGenreModif = -1;
			$idGenreNotif = -1;
			$notification = 'rien';
			
			// Traitement des actions POST (ajouter, modifier, supprimer)
			if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
				$action = $_POST['cmdAction'] ?? '';
				if ($action === 'ajouterNouveauGenre') {
					$libGenre = $_POST['txtLibGenre'] ?? '';
					$idSpecialiste = !empty($_POST['lstMembre']) ? (int)$_POST['lstMembre'] : null;
					if ($libGenre !== '') {
						$idNouveau = $db->ajouterGenre($libGenre, $idSpecialiste);
						$idGenreNotif = $idNouveau;
						$notification = 'Ajouté';
						// Recharger les données
						$tbGenres = $db->getLesGenresComplet();
					}
				} elseif ($action === 'demanderModifierGenre') {
					$idGenreModif = (int)($_POST['txtIdGenre'] ?? -1);
				} elseif ($action === 'validerModifierGenre') {
					$idGenre = (int)($_POST['txtIdGenre'] ?? -1);
					$libGenre = $_POST['txtLibGenre'] ?? '';
					$idSpecialiste = !empty($_POST['lstMembre']) ? (int)$_POST['lstMembre'] : null;
					if ($idGenre > 0 && $libGenre !== '') {
						$db->modifierGenre($idGenre, $libGenre, $idSpecialiste);
						$idGenreNotif = $idGenre;
						$notification = 'Modifié';
						$idGenreModif = -1;
						// Recharger les données
						$tbGenres = $db->getLesGenresComplet();
					}
				} elseif ($action === 'annulerModifierGenre') {
					$idGenreModif = -1;
				} elseif ($action === 'supprimerGenre') {
					$idGenre = (int)($_POST['txtIdGenre'] ?? -1);
					if ($idGenre > 0) {
						$db->supprimerGenre($idGenre);
						$idGenreNotif = $idGenre;
						$notification = 'Supprimé';
						// Recharger les données
						$tbGenres = $db->getLesGenresComplet();
					}
				}
			}
			
			echo $twig->render('lesGenres.html.twig', [
				'menuActif' => 'Jeux',
				'tbGenres' => $tbGenres,
				'tbMembres' => $tbMembres,
				'idGenreModif' => $idGenreModif,
				'idGenreNotif' => $idGenreNotif,
				'notification' => $notification
			]);
		} catch (Throwable $e) {
			echo '<div class="erreur">Erreur lors du chargement des genres : ' . htmlspecialchars($e->getMessage()) . '</div>';
		}
	}
break;
}
case 'gererPlateformes' :
{
    $cPlat = __DIR__ . '/controleur/c_gererPlateformes.php';
    if (file_exists($cPlat)) {
        require $cPlat;
    } else {
        try {
            $tbPlateformes = $db->getLesPlateformes();
            $idPlateformeModif = -1; $idPlateformeNotif = -1; $notification = 'rien';
            
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
                $action = $_POST['cmdAction'] ?? '';
                if ($action === 'ajouterNouvellePlateforme') {
                    $libPlateforme = $_POST['txtLibPlateforme'] ?? '';
                    if ($libPlateforme !== '') {
                        $idNouveau = $db->ajouterPlateforme($libPlateforme);
                        $idPlateformeNotif = $idNouveau;
                        $notification = 'Ajouté';
                        $tbPlateformes = $db->getLesPlateformes();
                    }
                } elseif ($action === 'demanderModifierPlateforme') {
                    $idPlateformeModif = (int)($_POST['txtIdPlateforme'] ?? -1);
                } elseif ($action === 'validerModifierPlateforme') {
                    $idPlateforme = (int)($_POST['txtIdPlateforme'] ?? -1);
                    $libPlateforme = $_POST['txtLibPlateforme'] ?? '';
                    if ($idPlateforme > 0 && $libPlateforme !== '') {
                        $db->modifierPlateforme($idPlateforme, $libPlateforme);
                        $idPlateformeNotif = $idPlateforme;
                        $notification = 'Modifié';
                        $idPlateformeModif = -1;
                        $tbPlateformes = $db->getLesPlateformes();
                    }
                } elseif ($action === 'annulerModifierPlateforme') {
                    $idPlateformeModif = -1;
                } elseif ($action === 'supprimerPlateforme') {
                    $idPlateforme = (int)($_POST['txtIdPlateforme'] ?? -1);
                    if ($idPlateforme > 0) {
                        $db->supprimerPlateforme($idPlateforme);
                        $idPlateformeNotif = $idPlateforme;
                        $notification = 'Supprimé';
                        $tbPlateformes = $db->getLesPlateformes();
                    }
                }
            }
            
            echo $twig->render('lesPlateformes.html.twig', [
                'menuActif' => 'Jeux',
                'tbPlateformes' => $tbPlateformes,
                'idPlateformeModif' => $idPlateformeModif,
                'idPlateformeNotif' => $idPlateformeNotif,
                'notification' => $notification
            ]);
        } catch (Throwable $e) {
            echo '<div class="erreur">Erreur plateformes: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
    break;
}
case 'gererMarques' :
{
    $cMarq = __DIR__ . '/controleur/c_gererMarques.php';
    if (file_exists($cMarq)) {
        require $cMarq;
    } else {
        try {
            $tbMarques = $db->getLesMarques();
            $idMarqueModif = -1; $idMarqueNotif = -1; $notification = 'rien';
            
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
                $action = $_POST['cmdAction'] ?? '';
                if ($action === 'ajouterNouvelleMarque') {
                    $nomMarque = $_POST['txtNomMarque'] ?? '';
                    if ($nomMarque !== '') {
                        $idNouveau = $db->ajouterMarque($nomMarque);
                        $idMarqueNotif = $idNouveau;
                        $notification = 'Ajouté';
                        $tbMarques = $db->getLesMarques();
                    }
                } elseif ($action === 'demanderModifierMarque') {
                    $idMarqueModif = (int)($_POST['txtIdMarque'] ?? -1);
                } elseif ($action === 'validerModifierMarque') {
                    $idMarque = (int)($_POST['txtIdMarque'] ?? -1);
                    $nomMarque = $_POST['txtNomMarque'] ?? '';
                    if ($idMarque > 0 && $nomMarque !== '') {
                        $db->modifierMarque($idMarque, $nomMarque);
                        $idMarqueNotif = $idMarque;
                        $notification = 'Modifié';
                        $idMarqueModif = -1;
                        $tbMarques = $db->getLesMarques();
                    }
                } elseif ($action === 'annulerModifierMarque') {
                    $idMarqueModif = -1;
                } elseif ($action === 'supprimerMarque') {
                    $idMarque = (int)($_POST['txtIdMarque'] ?? -1);
                    if ($idMarque > 0) {
                        $db->supprimerMarque($idMarque);
                        $idMarqueNotif = $idMarque;
                        $notification = 'Supprimé';
                        $tbMarques = $db->getLesMarques();
                    }
                }
            }
            
            echo $twig->render('lesMarques.html.twig', [
                'menuActif' => 'Jeux',
                'tbMarques' => $tbMarques,
                'idMarqueModif' => $idMarqueModif,
                'idMarqueNotif' => $idMarqueNotif,
                'notification' => $notification
            ]);
        } catch (Throwable $e) {
            echo '<div class="erreur">Erreur marques: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
    break;
}
case 'gererPegis' :
{
    $cPeg = __DIR__ . '/controleur/c_gererPegis.php';
    if (file_exists($cPeg)) {
        require $cPeg;
    } else {
        try {
            $tbPegis = $db->getLesPegis();
            $idPegiModif = -1; $idPegiNotif = -1; $notification = 'rien';
            
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
                $action = $_POST['cmdAction'] ?? '';
                if ($action === 'ajouterNouveauPegi') {
                    $age = $_POST['txtAge'] ?? '';
                    $description = $_POST['txtDescription'] ?? '';
                    if ($age !== '' && $description !== '') {
                        $idNouveau = $db->ajouterPegi($age, $description);
                        $idPegiNotif = $idNouveau;
                        $notification = 'Ajouté';
                        $tbPegis = $db->getLesPegis();
                    }
                } elseif ($action === 'demanderModifierPegi') {
                    $idPegiModif = (int)($_POST['txtIdPegi'] ?? -1);
                } elseif ($action === 'validerModifierPegi') {
                    $idPegi = (int)($_POST['txtIdPegi'] ?? -1);
                    $age = $_POST['txtAge'] ?? '';
                    $description = $_POST['txtDescription'] ?? '';
                    if ($idPegi > 0 && $age !== '' && $description !== '') {
                        $db->modifierPegi($idPegi, $age, $description);
                        $idPegiNotif = $idPegi;
                        $notification = 'Modifié';
                        $idPegiModif = -1;
                        $tbPegis = $db->getLesPegis();
                    }
                } elseif ($action === 'annulerModifierPegi') {
                    $idPegiModif = -1;
                } elseif ($action === 'supprimerPegi') {
                    $idPegi = (int)($_POST['txtIdPegi'] ?? -1);
                    if ($idPegi > 0) {
                        $db->supprimerPegis($idPegi);
                        $idPegiNotif = $idPegi;
                        $notification = 'Supprimé';
                        $tbPegis = $db->getLesPegis();
                    }
                }
            }
            
            echo $twig->render('lesPegis.html.twig', [
                'menuActif' => 'Jeux',
                'tbPegis' => $tbPegis,
                'idPegiModif' => $idPegiModif,
                'idPegiNotif' => $idPegiNotif,
                'notification' => $notification
            ]);
        } catch (Throwable $e) {
            echo '<div class="erreur">Erreur pegis: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
    break;
}
case 'gererJeux' :
{
    $cJeux = __DIR__ . '/controleur/c_gererJeux.php';
    if (file_exists($cJeux)) {
        require $cJeux;
    } else {
        try {
            $tbJeux = $db->getLesJeux();
            $tbGenres = $db->getLesGenres();
            $tbMarques = $db->getLesMarques();
            $tbPlateformes = $db->getLesPlateformes();
            $tbPegis = $db->getLesPegis();
            $notification = 'rien'; $refJeuModif = null; $refJeuNotif = null;
            
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
                $action = $_POST['cmdAction'] ?? '';
                if ($action === 'ajouterNouveauJeu') {
                    $refJeu = $_POST['refJeu'] ?? '';
                    $nom = $_POST['nom'] ?? '';
                    $prix = (float)($_POST['prix'] ?? 0);
                    $dateParution = $_POST['dateParution'] ?? '';
                    $idGenre = (int)($_POST['idGenre'] ?? 0);
                    $idMarque = (int)($_POST['idMarque'] ?? 0);
                    $idPlateforme = (int)($_POST['idPlateforme'] ?? 0);
                    $idPegi = (int)($_POST['idPegi'] ?? 0);
                    if ($refJeu !== '' && $nom !== '' && $dateParution !== '') {
                        $db->ajouterJeu($refJeu, $nom, $prix, $dateParution, $idGenre, $idMarque, $idPlateforme, $idPegi);
                        $refJeuNotif = $refJeu;
                        $notification = 'Ajouté';
                        $tbJeux = $db->getLesJeux();
                    }
                } elseif ($action === 'demanderModifierJeu') {
                    $refJeuModif = $_POST['refJeu'] ?? null;
                } elseif ($action === 'validerModifierJeu') {
                    $refJeu = $_POST['refJeu'] ?? '';
                    $nom = $_POST['nom'] ?? '';
                    $prix = (float)($_POST['prix'] ?? 0);
                    $dateParution = $_POST['dateParution'] ?? '';
                    $idGenre = (int)($_POST['idGenre'] ?? 0);
                    $idMarque = (int)($_POST['idMarque'] ?? 0);
                    $idPlateforme = (int)($_POST['idPlateforme'] ?? 0);
                    $idPegi = (int)($_POST['idPegi'] ?? 0);
                    if ($refJeu !== '' && $nom !== '' && $dateParution !== '') {
                        $db->modifierJeu($refJeu, $nom, $prix, $dateParution, $idGenre, $idMarque, $idPlateforme, $idPegi);
                        $refJeuNotif = $refJeu;
                        $notification = 'Modifié';
                        $refJeuModif = null;
                        $tbJeux = $db->getLesJeux();
                    }
                } elseif ($action === 'annulerModifierJeu') {
                    $refJeuModif = null;
                } elseif ($action === 'supprimerJeu') {
                    $refJeu = $_POST['refJeu'] ?? '';
                    if ($refJeu !== '') {
                        $db->supprimerJeu($refJeu);
                        $refJeuNotif = $refJeu;
                        $notification = 'Supprimé';
                        $tbJeux = $db->getLesJeux();
                    }
                }
            }
            
            echo $twig->render('lesJeux.html.twig', [
                'menuActif' => 'Jeux',
                'tbJeux' => $tbJeux,
                'tbGenres' => $tbGenres,
                'tbMarques' => $tbMarques,
                'tbPlateformes' => $tbPlateformes,
                'tbPegis' => $tbPegis,
                'notification' => $notification,
                'refJeuModif' => $refJeuModif,
                'refJeuNotif' => $refJeuNotif
            ]);
        } catch (Throwable $e) {
            echo '<div class="erreur">Erreur jeux: ' . htmlspecialchars($e->getMessage()) . '</div>';
		}
	}
break;
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
