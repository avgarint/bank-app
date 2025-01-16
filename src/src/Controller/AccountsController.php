<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Deposit;
use App\Entity\Transfer;
use App\Form\TransferType;
use App\Form\DepositType;
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
        $account->setAccountType('EPARGNE');
        $account->setBalance(0);
        $accountNumber = (new \DateTime())->format('dmHis');
        $account->setAccountNumber($accountNumber);

        $em->persist($account);
        $em->flush();

        return $this->redirectToRoute('app_accounts');
    }


    #[Route('/accounts/{id}/remove', name: 'app_accounts_remove')]
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


    #[Route('/accounts/{id}/transfer', name: 'transfer_create')]
    public function createTransfer(Account $account, EntityManagerInterface $em, Request $request): Response
    {
        $transfer = new Transfer();
        $transfer->setNoAccountEmitter($account->getAccountNumber());

        // Création du formulaire
        $form = $this->createForm(TransferType::class, $transfer, [
            'account_number' => $account->getAccountNumber(),
        ]);

        $form->handleRequest($request);

        // Traitement si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire
            $transfer = $form->getData();

            // Logique de mise à jour des comptes
            $emitterAccount = $account; // Compte émetteur (déjà fourni)
            $receiverAccountNumber = $transfer->getNoAccountReceiver(); // Numéro du compte récepteur
            $amount = $transfer->getAmountTransfer();

            // Trouver le compte récepteur
            $receiverAccount = $em->getRepository(Account::class)->findOneBy([
                'account_number' => $receiverAccountNumber,
            ]);

            if (!$receiverAccount) {
                $this->addFlash('error', 'Le compte récepteur est introuvable.');
                return $this->redirectToRoute('transfer_create', ['id' => $account->getId()]);
            }

            // Vérifier le solde du compte émetteur
            if ($emitterAccount->getBalance() < $amount) {
                $this->addFlash('error', 'Solde insuffisant pour effectuer le transfert.');
                return $this->redirectToRoute('transfer_create', ['id' => $account->getId()]);
            }

            // Mise à jour des soldes
            $emitterAccount->setBalance($emitterAccount->getBalance() - $amount);
            $receiverAccount->setBalance($receiverAccount->getBalance() + $amount);

            // Sauvegarder le transfert
            $em->persist($transfer);
            $em->persist($emitterAccount);
            $em->persist($receiverAccount);
            $em->flush();

            // Redirection avec message de succès
            $this->addFlash('success', 'Le transfert a été effectué avec succès.');
            return $this->redirectToRoute('app_accounts');
        }

        // Afficher le formulaire
        return $this->render('accounts/form.html.twig', [
            'formulaire' => $form->createView(),
            'action' => 'Ajouter',
        ]);
    }


    #[Route('/accounts/{id}/deposit', name: 'deposit_create')]
    public function createDeposit(Account $account, EntityManagerInterface $em, Request $request): Response
    {
        $deposit = new Deposit();
        $deposit->setNoAccountInvolve($account->getAccountNumber());

        // Création du formulaire
        $form = $this->createForm(DepositType::class, $deposit, [
            'account_number' => $account->getAccountNumber(),
        ]);

        $form->handleRequest($request);

        // Traitement si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire
            $deposit = $form->getData();

            // Logique de mise à jour du compte
            $account = $em->getRepository(Account::class)->find($account->getId());
            $amount = $deposit->getAmountDeposit();

            // Mise à jour du solde
            $account->setBalance($account->getBalance() + $amount);

            // Sauvegarder le dépôt
            $em->persist($deposit);
            $em->persist($account);
            $em->flush();

            // Redirection avec message de succès
            $this->addFlash('success', 'Le dépôt a été effectué avec succès.');
            return $this->redirectToRoute('app_accounts');
        }

        // Afficher le formulaire
        return $this->render('accounts/form.html.twig', [
            'formulaire' => $form->createView(),
            'action' => 'Déposer',
        ]);
    }
}
