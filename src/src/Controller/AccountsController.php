<?php

namespace App\Controller;

use App\Entity\Debit;
use App\Entity\Account;
use App\Entity\Deposit;
use App\Form\DebitType;
use App\Entity\Transfer;
use App\Form\DepositType;

use App\Form\TransferType;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


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
    public function new(EntityManagerInterface $entityManager, Request $request): Response
    {
        $account = new Account();
        $account->setAccountType('EPARGNE');
        $account->setBalance(0);
        $accountNumber = (new \DateTime())->format('dmHis');
        $account->setAccountNumber($accountNumber);

        $entityManager->persist($account);
        $entityManager->flush();

        return $this->redirectToRoute('app_accounts');
    }


    #[Route('/accounts/{id}/remove', name: 'app_accounts_remove')]
    public function remove(Account $account, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($account);
        $entityManager->flush();

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
    public function createTransfer(Account $account, EntityManagerInterface $entityManager, Request $request): Response
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
            $receiverAccount = $entityManager->getRepository(Account::class)->findOneBy([
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
            $entityManager->persist($transfer);
            $entityManager->persist($emitterAccount);
            $entityManager->persist($receiverAccount);
            $entityManager->flush();

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
    public function createDeposit(Account $account, EntityManagerInterface $entityManager, Request $request): Response
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
            $account = $entityManager->getRepository(Account::class)->find($account->getId());
            $amount = $deposit->getAmountDeposit();

            // Mise à jour du solde
            $account->setBalance($account->getBalance() + $amount);

            // Sauvegarder le dépôt
            $entityManager->persist($deposit);
            $entityManager->persist($account);
            $entityManager->flush();

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

    #[Route('/accounts/{id}/debit', name: 'debit_create')]
    public function createDebit(Account $account, EntityManagerInterface $em, Request $request): Response
    {
        $debit = new Debit();
        $debit->setNoAccountInvolve($account->getAccountNumber());

        // Création du formulaire
        $form = $this->createForm(DebitType::class, $debit, [
            'account_number' => $account->getAccountNumber(),
        ]);

        $form->handleRequest($request);

        // Traitement si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire
            $debit = $form->getData();

            // Logique de mise à jour du compte
            $account = $em->getRepository(Account::class)->find($account->getId());
            $amount = $debit->getAmountDebit();

            // Vérifier le solde
            if ($account->getBalance() < $amount) {
                $this->addFlash('error', 'Solde insuffisant pour effectuer le retrait.');
                return $this->redirectToRoute('debit_create', ['id' => $account->getId()]);
            }

            // Mise à jour du solde
            $account->setBalance($account->getBalance() - $amount);

            // Sauvegarder le retrait
            $em->persist($debit);
            $em->persist($account);
            $em->flush();

            // Redirection avec message de succès
            $this->addFlash('success', 'Le retrait a été effectué avec succès.');
            return $this->redirectToRoute('app_accounts');
        }

        // Afficher le formulaire
        return $this->render('accounts/form.html.twig', [
            'formulaire' => $form->createView(),
            'action' => 'Retirer',
        ]);
    }
}
