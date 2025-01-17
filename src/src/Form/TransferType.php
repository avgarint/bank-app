<?php

namespace App\Form;

use App\Entity\Transfer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TransferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('no_account_emitter', TextType::class, [
                'data' => $options['number'], // Préremplir avec la donnée passée
                'disabled' => true, // Rendre le champ non modifiable (facultatif)
            ])
            ->add('no_account_receiver', TextType::class, [
                'attr' => [
                    'maxlength' => 10,
                    'pattern' => '\d{10}', // Regex pattern to allow only 10 digit numbers
                ],
            ])
            ->add('amount')
            ->add('date')
            ->add('confirm', SubmitType::class, [
                'label' => 'Confirm',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transfer::class,
            'number' => null, // Option personnalisée
        ]);

        $resolver->setAllowedTypes('number', ['null', 'string']);
    }
}
