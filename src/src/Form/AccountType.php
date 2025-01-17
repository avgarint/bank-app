<?php

namespace App\Form;

use App\Entity\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Épargne' => 'EPARGNE',
                    'Courant' => 'COURANT',
                ],
                'label' => 'Type de compte',
            ])
            ->add('balance', IntegerType::class, [
                'attr' => [
                    'min' => 0,
                    'placeholder' => 'Solde initial',
                    'readonly' => true,
                ],
                'data' => 0,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer un compte',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Account::class,
        ]);
    }
}
