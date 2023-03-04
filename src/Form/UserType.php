<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('password')
            ->add('firstName')
            ->add('lastName')
            ->add('slug')
            ->add('location')
            ->add('avatar', VichFileType::class, [
                'required' => false,
                'label' => 'Votre image',
            ])
            ->add('registeredAt')
            ->add('niveau')
            ->add('description2')
            ->add('categories');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
