<?php
// si le paramètre action n'est pas positionné alors
// si aucun bouton "action" n'a été envoyé alors par défaut on affiche les genres
// sinon l'action est celle indiquée par le bouton
if (!isset($_POST['cmdAction'])){
$action = 'afficherGenres';
}
else {
// par défaut
$action = $_POST['cmdAction'];
}
$idGenreModif = -1; // positionné si demande de modification
$notification = 'rien'; // pour notifier la mise à jour dans la vue
$idGenreNotif = -1; // positionné si mise à jour dans la vue
// selon l'action demandée on réalise l'action
switch($action){
case 'ajouterNouveauGenre':{
if (!empty($_POST['txtLibGenre'])) {
$idGenreNotif = $db->ajouterGenre($_POST['txtLibGenre'], $_POST['lstMembre']);
$notification = 'Ajouté';
}
break;
}
case 'demanderModifierGenre':{
$idGenreModif = $_POST['txtIdGenre'];
break;
}
case 'validerModifierGenre':{
$db->modifierGenre($_POST['txtIdGenre'], $_POST['txtLibGenre'], $_POST['lstMembre']);
$idGenreNotif = $_POST['txtIdGenre'];
$notification = 'Modifié';
break;
}
case 'supprimerGenre':{
$idGenre = $_POST['txtIdGenre'];
$db->supprimerGenre($idGenre);
break;
}
}
// l' affichage des genres se fait dans tous les cas
$tbMembres = $db->getLesMembres();
$tbGenres = $db->getLesGenresComplet();
//require 'vue/v_lesGenres.php';
echo $twig->render('lesGenres.html.twig', array(
'menuActif' => 'Jeux',
'tbGenres' => $tbGenres,
'tbMembres' => $tbMembres,
'idGenreModif' => $idGenreModif,
'idGenreNotif' => $idGenreNotif,
'notification' => $notification
));
?>