<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Transfer;

use App\Repository\TransferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class TransfersController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/transfers', name: 'app_transfers')]
    public function index(TransferRepository $transferRepository): Response
    {
        $transfers = $transferRepository->findAll();

        return $this->render('transfers/index.html.twig', ['transfers' => $transfers]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/transfers/{id}', name: 'app_transfers_details')]
    public function details(Transfer $transfer): Response
    {
        return $this->render('transfers/details.html.twig', ['transfer' => $transfer]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/transfers/{id}/remove', name: 'app_transfers_remove')]
    public function remove(Transfer $transfer, EntityManagerInterface $entityManager): Response
    {
        // Récupérer les informations du transfert
        $emitterAccountNumber = $transfer->getNoAccountEmitter();
        $receiverAccountNumber = $transfer->getNoAccountReceiver();
        $amount = $transfer->getAmount();

        // Récupérer les comptes émetteur et récepteur
        $emitterAccount = $entityManager->getRepository(Account::class)->findOneBy([
            'number' => $emitterAccountNumber,
        ]);
        $receiverAccount = $entityManager->getRepository(Account::class)->findOneBy([
            'number' => $receiverAccountNumber,
        ]);

        if (!$emitterAccount || !$receiverAccount) {
            $this->addFlash('error', 'Impossible de trouver les comptes associés au transfert.');
            return $this->redirectToRoute('app_transfers');
        }

        // Vérifier que le compte récepteur a suffisamment de fonds pour "annuler" le transfert
        if ($receiverAccount->getBalance() < $amount) {
            $this->addFlash('error', 'Le compte récepteur n\'a pas suffisamment de fonds pour annuler ce transfert.');
            return $this->redirectToRoute('app_transfers');
        }

        // Annuler le transfert en inversant le montant
        $emitterAccount->setBalance($emitterAccount->getBalance() + $amount);
        $receiverAccount->setBalance($receiverAccount->getBalance() - $amount);

        // Supprimer le transfert
        $entityManager->remove($transfer);

        // Sauvegarder les changements
        $entityManager->flush();

        $this->addFlash('success', 'Le transfert a été annulé avec succès.');
        return $this->redirectToRoute('app_transfers');
    }
}
