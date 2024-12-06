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


class AdminAdvertController extends AbstractController
{
    public const ADVERT_PER_PAGE = 2;

    public function __construct(
        #[Target('advertStateMachine')]
        private WorkflowInterface $workflow
    )
    {
    }

    // #[Route('/admin/advert', name: 'app_advert')]
    // public function index(Request $request, AdvertRepository $advertRepository): Response
    // {
    //     if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
    //         return $this->redirectToRoute('app_login');
    //     }
    //     $offset = max(0, $request->query->getInt('offset', 0));

    //     $query = $advertRepository->createQueryBuilder('a')
    //         ->orderBy('a.creatadAt', 'DESC')
    //         ->getQuery();

    //     $paginator = new Paginator($query);
    //     $paginator->getQuery()->setFirstResult($offset);
    //     $paginator->getQuery()->setMaxResults(self::ADVERT_PER_PAGE);

    //     $previous = $offset - self::ADVERT_PER_PAGE;
    //     $next = min(count($paginator), $offset + self::ADVERT_PER_PAGE);

    //     return $this->render('advert/index.html.twig', [
    //         'adverts' => $paginator,
    //         'previous' => $previous,
    //         'next' => $next,
    //     ]);
    // }

    #[Route('/admin/advert/{id}', name: 'advert', requirements: ['id' => '\d+'])]
    public function show(Advert $advert): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }
        if (!$advert) {
            throw $this->createNotFoundException('Advert not found.');
        }

        return $this->render('advert/show.html.twig', [
            'advert' => $advert,
        ]);
    }

    #[Route('/admin/advert/new', name: 'advert_new', priority: 10)]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }
        $advert = new Advert();

        $form = $this->createForm(AdvertType::class, $advert);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($advert);
            $entityManager->flush();
            $this->addFlash(
                'notice',
                'Thinks for yout advert!'
            );

            return $this->redirectToRoute('app_advert');
        }

        return $this->render('advert/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
