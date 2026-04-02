<?php

namespace App\Form;

use App\Entity\ArtisticWork;
use App\Entity\Offer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $allowedTypes = $options['allowed_types'];

        $typeChoices = [];
        if (in_array('sale', $allowedTypes) || in_array('both', $allowedTypes)) {
            $typeChoices["Achat (faire une offre de prix)"] = 'purchase';
        }
        if (in_array('exchange', $allowedTypes) || in_array('both', $allowedTypes)) {
            $typeChoices["Échange (proposer une de mes œuvres)"] = 'exchange';
        }

        if (count($typeChoices) > 1) {
            $builder->add('type', ChoiceType::class, [
                'label'   => 'Type d\'offre',
                'choices' => $typeChoices,
                'expanded' => true,
                'attr'    => ['class' => 'offer-type-radio'],
            ]);
        } else {
            $builder->add('type', ChoiceType::class, [
                'label'   => false,
                'choices' => $typeChoices,
                'data'    => array_values($typeChoices)[0],
                'attr'    => ['class' => 'd-none'],
            ]);
        }

        $builder
            ->add('offerPrice', MoneyType::class, [
                'label'    => 'Votre prix proposé (€)',
                'currency' => 'EUR',
                'required' => false,
                'attr'     => ['placeholder' => 'Ex: 250'],
            ])
            ->add('proposedWork', EntityType::class, [
                'class'        => ArtisticWork::class,
                'choices'      => $options['user_works'],
                'choice_label' => 'name',
                'label'        => 'Œuvre à proposer en échange',
                'required'     => false,
                'placeholder'  => '-- Choisir une de vos œuvres --',
            ])
            ->add('offerMessage', TextareaType::class, [
                'label' => 'Message (optionnel)',
                'required' => false,
                'attr'  => ['rows' => 3, 'placeholder' => 'Présentez votre offre...'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'    => Offer::class,
            'allowed_types' => ['sale', 'exchange', 'both'],
            'user_works'    => [],
        ]);
    }
}
