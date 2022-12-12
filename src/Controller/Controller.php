<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Controller extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('/index.html.twig', [
            'controller_name' => 'Controller',
        ]);
    }
     #[Route('user/userprofile/{id}', name: 'userprofile')]
    public function userprofile(User $user): Response
    {
        if ($this->getUser()->getRoles()[0] == "ROLE_ADMIN"){
            return $this->redirectToRoute('admin', [
            'id' => $this->getUser()->getUserIdentifier()
        ]);
        }
        if (!$this->getUser() || $this->getUser()->getUserIdentifier() != $user->getId()) {
            throw $this->createAccessDeniedException();
        }
        
        return $this->render('/user/userprofile.html.twig', [
            'user' => $user
        ]);
    }
}