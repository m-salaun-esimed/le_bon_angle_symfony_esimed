<?php

namespace App\Controller;

use App\Entity\AdminUser;
use App\Form\AdminUserType;
use App\Form\AdminUserLoginType;

use App\Repository\AdminUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminUserController extends AbstractController
{   
    #[Route('/admin/user', name: 'app_admin_user')]
    public function index(AdminUserRepository $repository): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('admin_user/index.html.twig', [
            'admin_users' => $repository->findAll(),
        ]);
    }

    #[Route('/admin/new', name: 'app_admin_user_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
        //     return $this->redirectToRoute('app_login');
        // }
        $adminUser = new AdminUser();

        $form = $this->createForm(AdminUserType::class, $adminUser);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($adminUser);
            $entityManager->flush();
            $this->addFlash(
                'notice',
                'Your changes were saved!'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('admin_user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/{id}', name: 'app_admin_user_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(AdminUser $adminUser): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('admin_user/show.html.twig', [
            'admin_user' => $adminUser,
        ]);
    }

    #[Route('/admin/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AdminUser $adminUser, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }
        $form = $this->createForm(AdminUserType::class, $adminUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_user', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_user/edit.html.twig', [
            'admin_user' => $adminUser,
            'form' => $form,
        ]);
    }

    #[Route('/admin/{id}<\d+>', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, AdminUser $adminUser, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }
    
        if ($this->getUser() === $adminUser) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectToRoute('app_admin_user');
        }
    
        if ($this->isCsrfTokenValid('delete' . $adminUser->getId(), $request->request->get('_token'))) {
            $entityManager->remove($adminUser);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }
    
        return $this->redirectToRoute('app_admin_user', [], Response::HTTP_SEE_OTHER);
    }
    
}
