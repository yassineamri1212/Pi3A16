<?php

namespace App\Form;

use App\Entity\Offre;
use App\Entity\Parcour;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class OffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('parcour', EntityType::class, [
                'class' => Parcour::class,
                // --- REMOVED 'choice_label' => 'trajet', ---
                // By removing choice_label, it will use Parcour::__toString() for the dropdown text
                'placeholder' => '--- Select a Parcour ---',
                'attr' => ['class' => 'form-select']
            ])
            ->add('climatisee', CheckboxType::class, [
                'label' => 'Air Conditioned?',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('photoFile', VichImageType::class, [
                'label' => 'Photo (Image file, Optional)',
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Remove current photo?',
                'download_uri' => false,
                'image_uri' => true,
                'asset_helper' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('typeFuel', ChoiceType::class, [
                'label' => 'Fuel Type',
                'choices' => [
                    'Gasoline' => 'gasoline',
                    'Diesel' => 'diesel',
                    'Electric' => 'electric',
                    'Hybrid' => 'hybrid',
                    'LPG' => 'lpg',
                    'Other' => 'other',
                ],
                'placeholder' => '--- Select Fuel Type ---',
                'attr' => ['class' => 'form-select']
            ])
            ->add('nombrePlaces', IntegerType::class, [
                'label' => 'Number of Available Seats',
                'attr' => ['class' => 'form-control', 'min' => 1]
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Price per Seat (TND)',
                'html5' => true,
                'scale' => 2, // Allow decimals
                'attr' => [
                    'class' => 'form-control',
                    'step' => '0.100', // Step in TND
                    'min' => 0
                ]
            ])
            ->add('dateDepart', DateTimeType::class, [
                'label' => 'Departure Date and Time',
                'widget' => 'single_text', // Use HTML5 datetime-local input
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offre::class,
        ]);
    }
}