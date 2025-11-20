<?php

namespace App\Controller;

use App\Entity\Plateforme;
use App\Form\PlateformeType;
use App\Repository\PlateformeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/plateforme')]
final class PlateformeController extends AbstractController
{
    #[Route(name: 'app_plateforme_index', methods: ['GET'])]
    public function index(PlateformeRepository $plateformeRepository): Response
    {
        return $this->render('plateforme/index.html.twig', [
            'plateformes' => $plateformeRepository->findAll(),
            'menuActif' => 'Jeux',
        ]);
    }

    #[Route('/new', name: 'app_plateforme_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $plateforme = new Plateforme();
        $form = $this->createForm(PlateformeType::class, $plateforme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($plateforme);
            $entityManager->flush();

            return $this->redirectToRoute('app_plateforme_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('plateforme/new.html.twig', [
            'plateforme' => $plateforme,
            'form' => $form,
            'menuActif' => 'Jeux',
        ]);
    }

    #[Route('/{id}', name: 'app_plateforme_show', methods: ['GET'])]
    public function show(Plateforme $plateforme): Response
    {
        return $this->render('plateforme/show.html.twig', [
            'plateforme' => $plateforme,
            'menuActif' => 'Jeux',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_plateforme_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Plateforme $plateforme, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PlateformeType::class, $plateforme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_plateforme_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('plateforme/edit.html.twig', [
            'plateforme' => $plateforme,
            'form' => $form,
            'menuActif' => 'Jeux',
        ]);
    }

    #[Route('/{id}', name: 'app_plateforme_delete', methods: ['POST'])]
    public function delete(Request $request, Plateforme $plateforme, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$plateforme->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($plateforme);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_plateforme_index', [], Response::HTTP_SEE_OTHER);
    }
}
