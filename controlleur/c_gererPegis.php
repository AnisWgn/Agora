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
// affichage
$tbPegis = $db->getLesPegis();
echo $twig->render('lesPegis.html.twig', array(
'menuActif' => 'Jeux',
'tbPegis' => $tbPegis,
'idPegiModif' => $idPegiModif,
'idPegiNotif' => isset($idPegiNotif) ? $idPegiNotif : -1,
'notification' => $notification
));
?>