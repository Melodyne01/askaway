<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods:['POST'])]
    public function login()
    {
       $user = $this->getUser();
       return $this->json([
        'id' => $user->getUserIdentifier(),
        'roles' => $user->getRoles()
       ]);
    }
}
