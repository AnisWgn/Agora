<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class TournoisController extends AbstractController
{
    #[Route('/tournois', name: 'tournois_afficher')]
    public function index(SessionInterface $session): Response
    {
        // Vérification de l'authentification (utilise la session PHP native ou Symfony)
        if (isset($_SESSION['idUtilisateur']) || $session->has('idUtilisateur')) {
            return $this->render('tournois/index.html.twig', [
                'controller_name' => 'TournoisController',
            ]);
        } else {
            return $this->render('connexion.html.twig');
        }
    }
}
