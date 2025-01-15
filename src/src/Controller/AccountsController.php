<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Transfer;
use App\Form\TransferType;
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

    // #[Route('/transfer/new', name: 'transfer_create')]
    // public function createTransfer(EntityManagerInterface $em, Request $request): Response
    // {
    //     $transfer = new Transfer;

    //     $form = $this->createForm(TransferType::class, $transfer);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $em->persist($transfer);
    //         $em->flush();

    //         // Récupérer les données du formulaire
    //         $data = $form->getData();

    //         // Trouver le compte spécifique (par exemple, par ID)
    //         $accountId = $data->getAccountId(); // Assurez-vous que cette méthode existe dans votre entité Transfer
    //         $account = $em->getRepository(Account::class)->find($accountId);

    //         if ($account) {
    //             // Modifier les données du compte
    //             $account->setBalance($account->getBalance() + $data->getAmount()); // Exemple de modification

    //             // Sauvegarder les modifications
    //             $em->persist($account);
    //             $em->flush();
    //         }

    //         return $this->redirectToRoute('app_accounts');
    //     }

    //     return $this->render('accounts/form.html.twig', [
    //         'formulaire' => $form,
    //         'action' => 'Ajouter',
    //     ]);
    // }
}
