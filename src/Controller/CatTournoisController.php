<?php

namespace App\Controller;

use App\Entity\CatTournois;
use App\Form\CatTournoisType;
use App\Repository\CatTournoisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cat/tournois')]
final class CatTournoisController extends AbstractController
{
    #[Route(name: 'app_cat_tournois_index', methods: ['GET'])]
    public function index(CatTournoisRepository $catTournoisRepository): Response
    {
        return $this->render('cat_tournois/index.html.twig', [
            'cat_tournois' => $catTournoisRepository->findAll(),
            'menuActif' => 'Tournois',
        ]);
    }

    #[Route('/new', name: 'app_cat_tournois_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $catTournoi = new CatTournois();
        $form = $this->createForm(CatTournoisType::class, $catTournoi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($catTournoi);
            $entityManager->flush();

            return $this->redirectToRoute('app_cat_tournois_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cat_tournois/new.html.twig', [
            'cat_tournoi' => $catTournoi,
            'form' => $form,
            'menuActif' => 'Tournois',
        ]);
    }

    #[Route('/{id}', name: 'app_cat_tournois_show', methods: ['GET'])]
    public function show(CatTournois $catTournoi): Response
    {
        return $this->render('cat_tournois/show.html.twig', [
            'cat_tournoi' => $catTournoi,
            'menuActif' => 'Tournois',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cat_tournois_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CatTournois $catTournoi, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CatTournoisType::class, $catTournoi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_cat_tournois_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cat_tournois/edit.html.twig', [
            'cat_tournoi' => $catTournoi,
            'form' => $form,
            'menuActif' => 'Tournois',
        ]);
    }

    #[Route('/{id}', name: 'app_cat_tournois_delete', methods: ['POST'])]
    public function delete(Request $request, CatTournois $catTournoi, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$catTournoi->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($catTournoi);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cat_tournois_index', [], Response::HTTP_SEE_OTHER);
    }
}
