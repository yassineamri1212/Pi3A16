<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email; // Correct constraint namespace
use Symfony\Component\Validator\Constraints\NotBlank; // Correct constraint namespace

class ResetPasswordRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => false, // Label rendered manually or via form_row
                'attr' => [
                    'autocomplete' => 'email',
                    'class' => 'form-control', // Add Bootstrap class
                    'placeholder' => 'Enter your email address' // Add placeholder
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your email address',
                    ]),
                    new Email([ // Make sure Email constraint is used
                        'message' => 'Please enter a valid email address',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // No data_class needed as it's not mapped directly
        $resolver->setDefaults([]);
    }
}