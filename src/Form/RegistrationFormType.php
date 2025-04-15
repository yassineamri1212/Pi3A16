<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType; // For password confirmation
use Symfony\Component\Form\Extension\Core\Type\TextType; // For username
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userName', TextType::class, [ // Add the username field
                'label' => 'Username',
                'attr' => ['autocomplete' => 'username'],
                // Constraints are already on the User entity
            ])
            ->add('email', EmailType::class, [ // Specify EmailType
                'label' => 'Email Address',
                'attr' => ['autocomplete' => 'email'],
                // Constraints are already on the User entity
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'I agree to the <a href="/terms" target="_blank">terms of service</a>', // Example link
                'label_html' => true, // Allow HTML in the label
                'mapped' => false, // Don't try to save this to the User entity
                'constraints' => [
                    new IsTrue([
                        'message' => 'You must agree to our terms.',
                    ]),
                ],
                'row_attr' => ['class' => 'form-check mb-3'], // Apply Bootstrap classes to the row
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check-input'],
            ])
            // Use RepeatedType for password confirmation
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false, // Don't map directly to the User entity's password field
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6, // Minimum password length
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                'first_options' => [
                    'label' => 'Password',
                    'attr' => ['class' => 'form-control mb-1'] // Add margin bottom
                ],
                'second_options' => [
                    'label' => 'Repeat Password',
                    'attr' => ['class' => 'form-control']
                ],
                'invalid_message' => 'The password fields must match.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}