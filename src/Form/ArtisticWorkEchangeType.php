<?php

namespace App\Form;

use App\Entity\ArtisticWorkEchange;
use Symfony\Component\Form\AbstractType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtisticWorkEchangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('pictureFile', VichFileType::class, [
                'required' => false,
                'label' => 'Votre image',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ArtisticWorkEchange::class,
            'translation_domain' => 'forms',
        ]);
    }
}
