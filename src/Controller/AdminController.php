<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    #[Route('admin/profile/{id}', name: 'admin')]
    public function admin(User $user): Response
    {
        if ($this->getUser()->getUserIdentifier() != strval($user->getId())) {
            throw $this->createAccessDeniedException();
        }
        return $this->render('admin/admin.html.twig', [
           'user' =>$user
        ]);
    }

    #[Route('admin/addArticle', name: 'addArticle')]
    public function addArticle(User $user): Response
    {
        if ($this->getUser()->getUserIdentifier() != strval($user->getId())) {
            throw $this->createAccessDeniedException();
        }


        return $this->render('admin/addArticle.html.twig', [
           'user' =>$user
        ]);
    }
}