<?php

namespace App\Form;

use App\Controller\ChangePassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType as SymfonyPasswordType;

class PasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oldPassword', SymfonyPasswordType::class, [
                'attr' => [
                    'placeholder' => 'Votre mot de passe actuel'
                ]
            ])
            ->add('password', SymfonyPasswordType::class, [
                'attr' => [
                    'placeholder' => 'Votre nouveau mot de passe'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ChangePassword::class,
            'translation_domain' => 'forms',
        ]);
    }
}