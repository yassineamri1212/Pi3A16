<?php

namespace App\Form;

use App\Entity\Parcour;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType; // Ensure this is used
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParcourType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Parcour Name (Optional)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., Work Commute, Airport Run']
            ])
            ->add('pickup', TextType::class, [ // Standard TextType
                'label' => 'Pickup Address',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter address or set on map']
                // ID ('parcour_pickup') is added automatically by Symfony
            ])
            ->add('destination', TextType::class, [ // Standard TextType
                'label' => 'Destination Address',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter address or set on map']
                // ID ('parcour_destination') is added automatically by Symfony
            ])
            // Coordinates remain hidden
            ->add('latitudePickup', HiddenType::class)
            ->add('longitudePickup', HiddenType::class)
            ->add('latitudeDestination', HiddenType::class)
            ->add('longitudeDestination', HiddenType::class)

            // Distance and Time remain readonly
            ->add('distance', NumberType::class, [
                'label' => 'Distance (km)',
                'required' => false,
                'html5' => true,
                'scale' => 2,
                'attr' => [
                    'class' => 'form-control distance-field',
                    'readonly' => true,
                    'placeholder' => 'Calculated from map...'
                ]
            ])
            ->add('time', IntegerType::class, [
                'label' => 'Estimated Time (minutes)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control time-field',
                    'readonly' => true,
                    'placeholder' => 'Calculated from map...'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parcour::class,
        ]);
    }
}