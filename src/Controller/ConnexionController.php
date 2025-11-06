<?php
// src/Controller/ConnexionController.php
namespace App\Controller;
require_once 'modele/class.PdoJeux.inc.php';
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use PdoJeux;
class ConnexionController extends AbstractController
{
#[Route('/connexion/valider', name: 'connexion_valider')]
public function validerConnexion(SessionInterface $session, Request $request)
{
$login = (string) $request->request->get('txtLogin');
$mdpSaisi = (string) $request->request->get('txtMdp');
$mdpHacheClient = (string) $request->request->get('hdMdp');
// Si le JS de hachage n'est pas chargé, on hache côté serveur
$mdpPourVerif = $mdpHacheClient !== '' ? $mdpHacheClient : hash('sha512', $mdpSaisi);

// Vérification base de données
try {
    $db = PdoJeux::getPdoJeux();
    $utilisateur = $db->getUnMembre($login, $mdpPourVerif);
    
    // si l'utilisateur n'existe pas
    if (!$utilisateur) {
        // positionner le message d'erreur
        $this->addFlash(
            'danger', 'Login ou mot de passe incorrect !'
        );
        return $this->render('connexion.html.twig');
    } else {
        // créer trois variables de session pour id utilisateur, nom et prénom
        $session->set('idUtilisateur', $utilisateur->idMembre);
        $session->set('nomUtilisateur', $utilisateur->nomMembre);
        $session->set('prenomUtilisateur', $utilisateur->prenomMembre);
        // redirection du navigateur vers la page d'accueil
        return $this->redirectToRoute('accueil');
    }
} catch (\Exception $e) {
    $this->addFlash('danger', 'Erreur de connexion à la base de données : ' . $e->getMessage());
    return $this->render('connexion.html.twig');
}
}
#[Route('/deconnexion', name: 'deconnexion')]
public function deconnexion(SessionInterface $session)
{
// supprimer la session
$session->clear();
$session->invalidate();
// redirection vers l'accueil
return $this->redirectToRoute('accueil');
}
}
?>
