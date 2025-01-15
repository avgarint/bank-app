<?php

namespace App\Controller;

use App\Entity\Account;
use App\Repository\AccountRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

final class AccountsController extends AbstractController
{
    #[Route('/accounts', name: 'app_accounts')]
    public function index(AccountRepository $accountRepository): Response
    {
        $accounts = $accountRepository->findAll();

        return $this->render('accounts/index.html.twig', [
            'accounts' => $accounts,
        ]);
    }

    #[Route('/accounts/new', name: 'app_accounts_new')]
    public function new(EntityManagerInterface $em, Request $request): Response
    {
        $account = new Account();
        $account->setAccountType('Savings');
        $account->setBalance(1000);
        $account->setAccountNumber('1234567890');

        $em->persist($account);
        $em->flush();

        return $this->redirectToRoute('app_accounts');
    }

    #[Route('/accounts/{id}/edit', name: 'app_accounts_edit')]
    public function remove(Account $account, EntityManagerInterface $em): Response
    {
        $em->remove($account);
        $em->flush();

        return $this->redirectToRoute('app_accounts');
    }

    #[Route('/accounts/{id}', name: 'app_accounts_details')]
    public function details(Account $account): Response
    {
        return $this->render('accounts/details.html.twig', [
            'account' => $account,
        ]);
    }
}
