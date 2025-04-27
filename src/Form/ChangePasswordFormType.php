<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType; // Use RepeatedType
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [ // Use RepeatedType for confirmation
                'type' => PasswordType::class, // Base type is Password
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'class' => 'form-control mb-1' // Add Bootstrap class, maybe bottom margin
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a password',
                        ]),
                        new Length([
                            'min' => 8, // Adjust min length as needed (recommend at least 8)
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                    'label' => 'New password',
                ],
                'second_options' => [
                    'label' => 'Repeat Password',
                    'attr' => [
                        'autocomplete' => 'new-password', // Add autocomplete here too
                        'class' => 'form-control' // Add Bootstrap class
                    ],
                ],
                'invalid_message' => 'The password fields must match.',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller (`reset` action)
                'mapped' => false, // Don't try to map this to an entity property
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // No data_class needed as it's not mapped
        $resolver->setDefaults([]);
    }
}