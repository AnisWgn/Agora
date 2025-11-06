<?php
/**
* Page d'accueil de l'application AgoraBo
* Point d'entrée unique de l'application
* @author MD
* @package default
*/
// démarrer la session !!!!!!!! A FAIRE AVANT TOUT CODE HTML !!!!!!!!
// start session before sending any output
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
// suppress deprecation notices coming from vendor code (temporary)
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require __DIR__ . '/../templates/v_headerTwig.html'; // balise <head> et styles
// inclure les bibliothèques de fonctions
require_once __DIR__ . '/../src/Controller/modele/class.PdoJeux.inc.php';
//require_once 'include/_forms.inc.php';
// *** pour twig ***
// la directive "require 'vendor/autoload.php';" est ajoutée au début de l'application
// elle permet de charger le script "autoload.php".
// Ce script a été crée par composer et permet de charger les dépendances une à une dans le projet
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env into $_ENV/$_SERVER when present
if (file_exists(__DIR__ . '/../.env')) {
	// use Symfony Dotenv component (installed via composer) to populate env vars
	try {
		(new \Symfony\Component\Dotenv\Dotenv())->bootEnv(__DIR__ . '/../.env');
	} catch (Throwable $e) {
		// If Dotenv is not available or fails, continue — PdoJeux will report missing vars.
	}
}
// la classe FileSystemLoader permet de charger des fichiers contenus dans le dossier indiqué en paramètre
// point loader to the project's `templates` directory (absolute path)
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
// la classe Environment permet de stocker la configuration de l'environnement
// en phase de développement (debug) nous n'utilisons pas le cache
$twig = new \Twig\Environment($loader, array(
	// use defined constants when available; otherwise fall back to safe defaults
	'cache' => (defined('TWIG_CACHE') ? TWIG_CACHE : false),
	'debug' => (defined('TWIG_DEBUG') ? TWIG_DEBUG : false),
));
// pour que twig connaisse la variable globale de session
$twig->addGlobal('session', $_SESSION);

// Provide a simple `asset()` function for templates (fallback when Symfony Asset extension is not registered)
$twig->addFunction(new \Twig\TwigFunction('asset', function (string $path) {
	// if absolute URL, return as-is
	if (preg_match('#^(https?:)?//#', $path)) {
		return $path;
	}
	// Templates reference assets under 'assets/...'; ensure leading slash
	return '/' . ltrim($path, '/');
}));

// Provide a minimal `path()` function as a fallback for simple route names used in the templates
$twig->addFunction(new \Twig\TwigFunction('path', function (string $routeName) {
	$map = [
		'accueil' => 'index.php',
		'deconnexion' => 'index.php?uc=deconnexion',
		'genres_afficher' => 'index.php?uc=gererGenres&action=afficherGenres',
		// add more mappings here if your templates use other route names
	];
	return $map[$routeName] ?? ('index.php');
}));
// *** twig ***
// Connexion au serveur et à la base (A)
$db = PdoJeux::getPdoJeux();
// Si aucun utilisateur connecté, on considère que la page demandée est la page de connexion
// $_SESSION['idUtilisateur'] est crée lorsqu'un utilisateur autorisé se connecte (dans c_connexion.php)
if (!isset($_SESSION['idUtilisateur'])){
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
// Si uc non défini, on considère que la page demandée est la page d'accueil
if (!isset($_GET['uc'])) {
$_GET['uc'] = 'index';
}
// Récupère l'identifiant de la page passé via l'URL
$uc = $_GET['uc'];
// selon la valeur du use case demandé(uc) on inclut le contrôleur secondaire
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
// Fermeture de la connexion (C)
$db = null;
// pied de page
//require("vue/v_footer.html") ;
?>
