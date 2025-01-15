<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

final class ClientsController extends AbstractController
{
    #[Route('/clients', name: 'app_clients')]
    public function index(UserRepository $userRepository): Response
    {
        $clients = $userRepository->findAll();

        return $this->render('clients/index.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/clients/{id}/remove', name: 'app_clients_remove')]
    public function remove(User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('app_clients');
    }

    #[Route('/clients/{id}', name: 'app_clients_details')]
    public function details(User $user): Response
    {
        return $this->render('clients/details.html.twig', [
            'client' => $user,
        ]);
    }
}
