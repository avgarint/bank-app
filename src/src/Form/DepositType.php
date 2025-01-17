<?php

namespace App\Form;

use App\Entity\Deposit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DepositType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('no_account_involve', TextType::class, [
                'data' => $options['number'], // Préremplir avec la donnée passée
                'disabled' => true, // Rendre le champ non modifiable (facultatif)
            ])
            ->add('amount')
            ->add('id_account', TextType::class, [
                'data' => $options['account_id'], // Préremplir avec l'ID du compte
                'disabled' => true, // Rendre le champ non modifiable (facultatif)
            ])
            ->add('date', TextType::class, [
                'data' => (new \DateTime())->format('Y-m-d'), // Préremplir avec la date du jour
                'disabled' => true, // Rendre le champ non modifiable (facultatif)
            ])
            ->add('confirm', SubmitType::class, [
                'label' => 'Confirm',
            ]);;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Deposit::class,
            'number' => null, // Option personnalisée
            'account_id' => null, // Option personnalisée pour l'ID du compte
        ]);

        $resolver->setAllowedTypes('number', ['null', 'string']);
        $resolver->setAllowedTypes('account_id', ['null', 'int']);
    }
}
