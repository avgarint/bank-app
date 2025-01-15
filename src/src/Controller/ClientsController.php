<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClientsController extends AbstractController
{
    #[Route('/clients', name: 'app_clients')]
    public function index(): Response
    {
        return $this->render('clients/index.html.twig', [
            'controller_name' => 'ClientsController',
        ]);
    }

    #[Route('/clients/{id}', name: 'app_clients_details')]
    public function details(User $user): Response
    {
        return $this->render('clients/details.html.twig', [
            'user' => $user,
        ]);
    }
}
