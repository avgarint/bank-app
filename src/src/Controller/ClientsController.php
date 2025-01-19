<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Account;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ClientsController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/clients', name: 'app_clients')]
    public function index(UserRepository $userRepository): Response
    {
        $clients = $userRepository->findAll();

        return $this->render('clients/index.html.twig', ['clients' => $clients]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/clients/{id}', name: 'app_clients_details')]
    public function details(User $user, EntityManagerInterface $entityManager): Response
    {
        // Récupérer les comptes associés à l'utilisateur
        $accounts = $entityManager->getRepository(Account::class)->findBy(['user' => $user]);

        // Renvoyer la vue avec les informations du client et ses comptes
        return $this->render('clients/details.html.twig', [
            'client' => $user,
            'accounts' => $accounts,
        ]);
    }


    #[IsGranted('ROLE_ADMIN')]
    #[Route('/clients/{id}/remove', name: 'app_clients_remove')]
    public function remove(User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_clients');
    }
}
