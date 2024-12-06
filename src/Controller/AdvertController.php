<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Twig\Environment;
use App\Repository\AdvertRepository;
use App\Entity\Advert;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Form\AdvertType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/advert')]
final class AdvertController extends AbstractController
{
    public const ADVERT_PER_PAGE = 2;

    public function __construct(
        #[Target('advertStateMachine')]
        private WorkflowInterface $workflow
    )
    {
    }

    #[Route(name: 'app_advert_index', methods: ['GET'])]
    public function index(Request $request, AdvertRepository $advertRepository): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }
        $offset = max(0, $request->query->getInt('offset', 0));

        $query = $advertRepository->createQueryBuilder('a')
            ->orderBy('a.creatadAt', 'DESC')
            ->getQuery();

        $paginator = new Paginator($query);
        $paginator->getQuery()->setFirstResult($offset);
        $paginator->getQuery()->setMaxResults(self::ADVERT_PER_PAGE);

        $previous = $offset - self::ADVERT_PER_PAGE;
        $next = min(count($paginator), $offset + self::ADVERT_PER_PAGE);

        return $this->render('advert/index.html.twig', [
            'adverts' => $paginator,
            'previous' => $previous,
            'next' => $next,
        ]);
    }

    #[Route('/new', name: 'app_advert_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $advert = new Advert();
        $advert->setState('draft');
        $form = $this->createForm(AdvertType::class, $advert);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($advert);
            $entityManager->flush();
            $this->addFlash(
                'notice',
                'Thinks for yout advert!'
            );

            return $this->redirectToRoute('app_advert_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('advert/new.html.twig', [
            'advert' => $advert,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_advert_show', methods: ['GET'])]
    public function show(Advert $advert): Response
    {
        return $this->render('advert/show.html.twig', [
            'advert' => $advert,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_advert_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Advert $advert, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdvertType::class, $advert);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_advert_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('advert/edit.html.twig', [
            'advert' => $advert,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_advert_delete', methods: ['POST'])]
    public function delete(Request $request, Advert $advert, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$advert->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($advert);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_advert_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/admin/advert/{id}/publish', name: 'advert_publish')]
    public function publish(Advert $advert, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }
        if (!$this->workflow->can($advert, 'publish')) {
            throw $this->createAccessDeniedException('Cette annonce ne peut pas être publiée.');
        }

        $this->workflow->apply($advert, 'publish');
        $entityManager->persist($advert);
        $entityManager->flush();

        $this->addFlash('success', 'Annonce publiée avec succès.');

        return $this->redirectToRoute('app_advert');
    }

    #[Route('/admin/advert/{id}/reject', name: 'advert_reject')]
    public function reject(Advert $advert, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }
        if (!$this->workflow->can($advert, 'reject_draft') && !$this->workflow->can($advert, 'reject_published')) {
            throw $this->createAccessDeniedException('Cette annonce ne peut pas être rejetée.');
        }

        $transition = $advert->getState() === 'draft' ? 'reject_draft' : 'reject_published';

        $this->workflow->apply($advert, $transition);
        $entityManager->flush();

        $this->addFlash('success', 'Annonce rejetée avec succès.');

        return $this->redirectToRoute('app_advert');
    }
}
