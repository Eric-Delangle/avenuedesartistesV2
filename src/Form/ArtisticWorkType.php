<?php

namespace App\Form;

use App\Entity\ArtisticWork;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Vich\UploaderBundle\Form\Type\VichFileType;

class ArtisticWorkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('pictureFile', VichFileType::class, [
                'required' => false,
                'label' => 'Votre image',
            ])
            ->add('listingType', ChoiceType::class, [
                'label' => 'Type d\'annonce',
                'choices' => $options['can_sell']
                    ? [
                        'Vitrine uniquement' => 'none',
                        'En vente' => 'sale',
                        'En échange' => 'exchange',
                        'Vente et échange' => 'both',
                    ]
                    : [
                        'Vitrine uniquement' => 'none',
                        'En échange' => 'exchange',
                    ],
                'required' => true,
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix de vente',
                'currency' => 'EUR',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: 150.00'],
            ])
            ->add('exchangeDescription', TextareaType::class, [
                'label' => 'Ce que vous souhaitez en échange',
                'required' => false,
                'attr' => ['rows' => 3, 'placeholder' => 'Décrivez ce que vous souhaitez recevoir en échange...'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ArtisticWork::class,
            'translation_domain' => 'forms',
            'can_sell' => false,
        ]);
    }
}