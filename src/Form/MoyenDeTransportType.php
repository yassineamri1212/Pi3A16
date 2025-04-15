<?php
// src/Form/MoyenDeTransportType.php
namespace App\Form;

use App\Entity\MoyenDeTransport;
use App\Entity\Evenement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType; // Use Textarea for departure point
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoyenDeTransportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', TextType::class, [
                'label' => 'Transport Type',
                'required' => true, // Matches NotBlank constraint
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., Bus, Minivan, Car']
            ])
            ->add('prix', IntegerType::class, [
                'label' => 'Price per Seat (TND)',
                'required' => true, // Matches NotNull constraint
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter price...'
                    // Removed 'min' attribute as requested
                ]
            ])
            ->add('nbrePlaces', IntegerType::class, [
                'label' => 'Total Number of Seats',
                'required' => true, // Matches NotNull constraint
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter number of seats...'
                    // Removed 'min' attribute as requested
                ]
            ])
            // ---> ADDED FIELD <---
            ->add('pointDepart', TextareaType::class, [
                'label' => 'Departure Point / Meeting Location',
                'required' => true, // Matches NotBlank constraint
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Provide clear details, e.g., "Meet at the main bus station, platform 5, near the information desk."'
                ],
                'help' => 'Where should attendees gather for this transport?',
            ])
            // ---> END ADDED FIELD <---
            ->add('evenement', EntityType::class, [
                'class' => Evenement::class,
                'choice_label' => 'nom',
                'placeholder' => 'Assign to an event (Optional)',
                'required' => false, // Keep false if it can be unassigned
                'attr' => ['class' => 'form-select']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MoyenDeTransport::class,
        ]);
    }
}