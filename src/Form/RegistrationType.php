<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('password', PasswordType::class, [
                'attr' => [
                    'required' => false,
                    'style' => 'placeho',
                ]
            ])
            ->add('password_verify', PasswordType::class, [
                'attr' => [
                    'required' => false,
                    'style' => 'placeho',
                    'placeholder' => 'Tapez de nouveau votre mot de passe.'
                ]
            ])
            ->add('firstName')
            ->add('lastName')
            ->add('location')
            ->add('description2', TextType::class, [
                'attr' => [
                    'placeholder' => 'Décrivez vous en quelques mots (facultatif).'
                ]
            ])
            ->add('tel', TextType::class, [
                'label' => 'Votre numéro de téléphone.',
                'attr' => [
                    'placeholder' => 'Numéro de téléphone.'
                ]
            ])
            ->add('adress', TextType::class, [
                'label' => 'Votre adresse',
                'attr' => [
                    'placeholder' => 'Votre numéro et le nom de votre rue.'
                ]
            ])
            ->add('postalCode', TextType::class, ['label' => 'Votre code postal.', 'attr' => [
                'placeholder' => 'Code postal.'
            ]])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true
            ])
            ->add('avatarFile', VichFileType::class, [
                'required' => false,
                'label' => 'Votre avatar',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'forms',
        ]);
    }
}
