<?php
$menuActif = 'Jeux';
if (!isset($_POST['cmdAction'])) {
    $action = 'afficherPegis';
} else {
    $action = $_POST['cmdAction'];
}

$idPegiModif = -1;
$notification = 'rien';

switch($action) {
    case 'ajouterNouveauPegi': {
        if (!empty($_POST['txtAge']) && !empty($_POST['txtDescription'])) {
            $idPegiNotif = $db->ajouterPegi($_POST['txtAge'], $_POST['txtDescription']);
            $notification = 'Ajouté';
        }
        break;
    }
    case 'demanderModifierPegi': {
        $idPegiModif = $_POST['txtIdPegi'];
        break;
    }
    case 'validerModifierPegi': {
        $db->modifierPegi($_POST['txtIdPegi'], $_POST['txtAge'], $_POST['txtDescription']);
        $idPegiNotif = $_POST['txtIdPegi'];
        $notification = 'Modifié';
        break;
    }
    case 'supprimerPegi': {
        $db->supprimerPegis($_POST['txtIdPegi']);
        $notification = 'Supprimé';
        break;
    }
}

$tbPegis = $db->getLesPegis();

// --- DEBUT MODIFICATION : Remplacer l'ancien 'require' par ce bloc ---

// 1. Charger le chargeur automatique de Composer
require_once 'vendor/autoload.php';
$loader = new \Twig\Loader\FilesystemLoader('vue');

// 3. Initialiser l'environnement Twig
$twig = new \Twig\Environment($loader);

// 4. Définir les variables pour le template
$variables = [
    'menuActif' => $menuActif,
    'tbPegis' => $tbPegis,
    'idPegiModif' => $idPegiModif,
    'notification' => $notification,
];
// S'assurer que idPegiNotif est toujours défini pour éviter les erreurs Twig
if (isset($idPegiNotif)) {
    $variables['idPegiNotif'] = $idPegiNotif;
}

// 5. Rendre le template et l'afficher
echo $twig->render('v_lesPegis.html.twig', $variables);
?>