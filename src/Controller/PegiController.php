<?php

namespace App\Controller;

use App\Entity\Pegi;
use App\Form\PegiType;
use App\Repository\PegiRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pegi')]
final class PegiController extends AbstractController
{
    #[Route(name: 'app_pegi_index', methods: ['GET'])]
    public function index(PegiRepository $pegiRepository): Response
    {
        return $this->render('pegi/index.html.twig', [
            'pegis' => $pegiRepository->findAll(),
            'menuActif' => 'Jeux',
        ]);
    }

    #[Route('/new', name: 'app_pegi_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $pegi = new Pegi();
        $form = $this->createForm(PegiType::class, $pegi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($pegi);
            $entityManager->flush();

            return $this->redirectToRoute('app_pegi_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pegi/new.html.twig', [
            'pegi' => $pegi,
            'form' => $form,
            'menuActif' => 'Jeux',
        ]);
    }

    #[Route('/{id}', name: 'app_pegi_show', methods: ['GET'])]
    public function show(Pegi $pegi): Response
    {
        return $this->render('pegi/show.html.twig', [
            'pegi' => $pegi,
            'menuActif' => 'Jeux',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pegi_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pegi $pegi, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PegiType::class, $pegi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_pegi_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pegi/edit.html.twig', [
            'pegi' => $pegi,
            'form' => $form,
            'menuActif' => 'Jeux',
        ]);
    }

    #[Route('/{id}', name: 'app_pegi_delete', methods: ['POST'])]
    public function delete(Request $request, Pegi $pegi, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pegi->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($pegi);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_pegi_index', [], Response::HTTP_SEE_OTHER);
    }
}
