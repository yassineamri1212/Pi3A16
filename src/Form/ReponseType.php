<?php

namespace App\Form;

use App\Entity\Reponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reponse', TextareaType::class, [
                'label' => 'Your Response',
                // 'required' => true is implied by NotBlank constraint on entity
                'attr' => [
                    'rows' => 5,
                    'class' => 'form-control', // Add standard Bootstrap class
                    'placeholder' => 'Enter your response here...'
                    // No HTML5 validation attributes like required, minlength etc.
                ]
            ]);
        // Fields like date, reclamation, utilisateur_id, username are set in the Controller, not the form
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reponse::class,
            // Add novalidate attribute to disable browser validation
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}