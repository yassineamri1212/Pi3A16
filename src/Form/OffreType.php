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
// ---> ADD THIS LINE IF MISSING <---
use Vich\UploaderBundle\Form\Type\VichImageType;
// ---> END ADD <---

class OffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('parcour', EntityType::class, [
                'class' => Parcour::class,
                'choice_label' => 'trajet',
                'placeholder' => '--- Select a Parcour ---',
                'attr' => ['class' => 'form-select']
            ])
            ->add('climatisee', CheckboxType::class, [
                'label' => 'Air Conditioned?',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            // --- THIS IS THE CORRECT WAY TO ADD THE IMAGE UPLOAD FIELD ---
            ->add('photoFile', VichImageType::class, [ // Use VichImageType and the 'photoFile' property
                'label' => 'Photo (Image file, Optional)',
                'required' => false,        // Allow creating/editing without uploading/re-uploading
                'allow_delete' => true,     // Show checkbox to delete existing image
                'delete_label' => 'Remove current photo?', // Customize delete label
                'download_uri' => false,    // Don't show a download link
                'image_uri' => true,        // Show preview of the existing image (on edit)
                'asset_helper' => true,     // Use Symfony's asset() function
                'attr' => ['class' => 'form-control'] // Apply Bootstrap styling
            ])
            // --- END IMAGE FIELD ---
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
                    'step' => '0.100', // Step in TND (100 millimes)
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