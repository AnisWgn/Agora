<?php
/**
* Page d'accueil de l'application AgoraBo
* Point d'entrée unique de l'application
* @author MD
* @package default
*/
// démarrer la session !!!!!!!! A FAIRE AVANT TOUT CODE HTML !!!!!!!!
session_start();
//require 'vue/v_header.php'; // entête des pages HTML
require 'vue/v_headerTwig.html'; // balise <head> et styles
// inclure les bibliothèques de fonctions
require_once 'app/_config.inc.php';
require_once 'modele/class.PdoJeux.inc.php';
//require_once 'include/_forms.inc.php';
// *** pour twig ***
// la directive "require 'vendor/autoload.php';" est ajoutée au début de l'application
// elle permet de charger le script "autoload.php".
// Ce script a été crée par composer et permet de charger les dépendances une à une dans le projet
require_once 'vendor/autoload.php';
// la classe FileSystemLoader permet de charger des fichiers contenus dans le dossier indiqué en paramètre
$loader = new \Twig\Loader\FilesystemLoader('vue');
// la classe Environment permet de stocker la configuration de l'environnement
// en phase de développement (debug) nous n'utilisons pas le cache
$twig = new \Twig\Environment($loader, array(
'cache' => TWIG_CACHE,
'debug' => TWIG_DEBUG
));
// pour que twig connaisse la variable globale de session
$twig->addGlobal('session', $_SESSION);
// *** twig ***
// Connexion au serveur et à la base (A)
$db = PdoJeux::getPdoJeux();
// Accès public: définir un visiteur par défaut si non connecté
if (!isset($_SESSION['idUtilisateur'])) {
$_SESSION['idUtilisateur'] = 0;
$_SESSION['nomUtilisateur'] = '';
$_SESSION['prenomUtilisateur'] = 'Visiteur';
$twig->addGlobal('session', $_SESSION);
}
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
//require 'vue/v_menu.php';
echo $twig->render('accueil.html.twig');
break;
}
case 'gererGenres' :
{
//require 'vue/v_menu.php';
require 'controlleur/c_gererGenres.php';
break;
}
case 'gererJeux' :
{
// Page de gestion des jeux
require 'controlleur/c_gereJeux.php';
break;
}
case 'gererPlateformes' :
{
require 'controlleur/c_gerePlateforme.php';
break;
}
case 'gererMarques' :
{
require 'controlleur/c_gererMarque.php';
break;
}
case 'gererPegis' :
{
require 'controlleur/c_gererPegis.php';
break;
}
// ATTENTION, conserver les autres case dans votre code
case 'deconnexion' :
{
// Mode public: retourner à l'accueil
echo $twig->render('accueil.html.twig');
break;
}
}
// Fermeture de la connexion (C)
$db = null;
// pied de page
//require("vue/v_footer.html") ;
?>