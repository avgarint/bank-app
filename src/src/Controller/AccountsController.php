<?php

namespace App\Controller;

use App\Entity\Debit;
use App\Entity\Account;
use App\Entity\Deposit;
use App\Entity\User;
use App\Form\DebitType;
use App\Entity\Transfer;
use App\Form\AccountType;
use App\Form\DepositType;
use App\Form\TransferType;
use App\Repository\AccountRepository;
use App\Entity\AccountBalanceHistory;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

final class AccountsController extends AbstractController
{
    private function createBalanceHistoryRecord(Account $account, EntityManagerInterface $entityManager): void
    {
        $record = new AccountBalanceHistory();
        $record->setAccount($account);
        $record->setDate(new \DateTime());
        $record->setBalance($account->getBalance());
        $entityManager->persist($record);
    }

    #[Route('/accounts', name: 'app_accounts')]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        $currentUserEmail = $this->getUser()->getEmail();
        $currentUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $currentUserEmail]);

        $account = new Account();
        $account->setUser($currentUser);
        $account->setNumber((new \DateTime())->format('dmHis'));

        $accounts = $entityManager->getRepository(Account::class)->findBy(['user' => $account->getUser()]);

        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $accountCount = $entityManager->getRepository(Account::class)->count(['user' => $currentUser]);

            if ($accountCount >= 5) {
                $this->addFlash('error', 'Vous ne pouvez pas créer plus de 5 comptes.');
                return $this->redirectToRoute('app_accounts');
            }

            $entityManager->persist($account);
            $entityManager->flush();

            return $this->redirectToRoute('app_accounts');
        }

        return $this->render('accounts/index.html.twig', [
            'accounts' => $accounts,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/accounts/new', name: 'app_accounts_new')]
    public function new(EntityManagerInterface $entityManager, Request $request): Response
    {
        $account = new Account();
        $account->setType('EPARGNE');
        $account->setBalance(0);
        $account->setNumber((new \DateTime())->format('dmHis'));

        $entityManager->persist($account);
        $entityManager->flush();

        return $this->redirectToRoute('app_accounts');
    }

    #[Route('/accounts/{id}', name: 'app_accounts_details')]
    public function details(EntityManagerInterface $entityManager, Account $account): Response
    {
        return $this->render('accounts/details.html.twig', [
            'account' => $account,
            'transfers' => $entityManager->getRepository(Transfer::class)->findBy(['account' => $account]),
            'deposits' => $entityManager->getRepository(Deposit::class)->findBy(['account' => $account]),
            'debits' => $entityManager->getRepository(Debit::class)->findBy(['account' => $account])
        ]);
    }

    #[Route('/accounts/{id}/remove', name: 'app_accounts_remove')]
    public function remove(Account $account, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($account);
        $entityManager->flush();

        return $this->redirectToRoute('app_clients');
    }

    #[Route('/accounts/{id}/transfer', name: 'transfer_create')]
    public function createTransfer(Account $account, EntityManagerInterface $entityManager, Request $request): Response
    {
        $transfer = new Transfer();
        $transfer->setAccount($account);
        $transfer->setDate(new \DateTime());
        $transfer->setNoAccountEmitter($account->getNumber());

        // Création du formulaire
        $form = $this->createForm(TransferType::class, $transfer, [
            'number' => $account->getNumber()
        ]);

        $form->handleRequest($request);

        // Traitement si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire
            $transfer = $form->getData();

            // Logique de mise à jour des comptes
            $emitterAccount = $account; // Compte émetteur (déjà fourni)
            $receiverAccountNumber = $transfer->getNoAccountReceiver(); // Numéro du compte récepteur
            $amount = $transfer->getAmount();

            // Trouver le compte récepteur
            $receiverAccount = $entityManager->getRepository(Account::class)->findOneBy([
                'number' => $receiverAccountNumber,
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

            $this->createBalanceHistoryRecord($account, $entityManager);
            $this->createBalanceHistoryRecord($receiverAccount, $entityManager);

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
        $deposit->setAccount($account);
        $deposit->setDate(new \DateTime());
        $deposit->setNoAccountInvolve($account->getNumber());

        // Création du formulaire
        $form = $this->createForm(DepositType::class, $deposit, [
            'number' => $account->getNumber()
        ]);

        $form->handleRequest($request);

        // Traitement si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire
            $deposit = $form->getData();
            $amount = $deposit->getAmount();

            // Logique de mise à jour du compte
            $account = $entityManager->getRepository(Account::class)->find($account->getId());

            // Mise à jour du solde
            $account->setBalance($account->getBalance() + $amount);

            // Sauvegarder le dépôt
            $entityManager->persist($deposit);
            $entityManager->persist($account);

            $this->createBalanceHistoryRecord($account, $entityManager);

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
    public function createDebit(Account $account, EntityManagerInterface $entityManager, Request $request): Response
    {
        $debit = new Debit();
        $debit->setAccount($account);
        $debit->setDate(new \DateTime());
        $debit->setNoAccountInvolve($account->getNumber());

        // Création du formulaire
        $form = $this->createForm(DebitType::class, $debit, [
            'number' => $account->getNumber()
        ]);

        $form->handleRequest($request);

        // Traitement si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire
            $debit = $form->getData();

            // Logique de mise à jour du compte
            $account = $entityManager->getRepository(Account::class)->find($account->getId());
            $amount = $debit->getAmount();

            // Vérifier le solde
            if ($account->getBalance() < $amount) {
                $this->addFlash('error', 'Solde insuffisant pour effectuer le retrait.');
                return $this->redirectToRoute('debit_create', ['id' => $account->getId()]);
            }

            // Mise à jour du solde
            $account->setBalance($account->getBalance() - $amount);

            // Sauvegarder le retrait
            $entityManager->persist($debit);
            $entityManager->persist($account);

            $this->createBalanceHistoryRecord($account, $entityManager);

            $entityManager->flush();

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

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/accounts/{id}/chart', name: 'app_accounts_chart')]
    public function getChartData(Account $account, EntityManagerInterface $entityManager): JsonResponse
    {
        $records = $entityManager->getRepository(AccountBalanceHistory::class)
            ->findBy(['account' => $account], ['date' => 'ASC']);

        $labels = [];
        $values = [];

        foreach ($records as $record) {
            $labels[] = $record->getDate()->format('Y-m-d');
            $values[] = $record->getBalance();
        }

        return $this->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }
}
