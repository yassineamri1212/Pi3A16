<?php
// src/Form/ProfileFormType.php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userName', TextType::class, [
                'label' => 'Username',
                // Constraints are already on the User entity, Form will use them
                'attr' => ['class' => 'form-control']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                // Often email shouldn't be changed easily or is the identifier
                // You could make it disabled if it cannot be changed:
                'disabled' => true,
                'attr' => ['class' => 'form-control-plaintext'] // Style as plain text if disabled
                // If changeable, use: 'attr' => ['class' => 'form-control']
            ])
            // Add password change fields using RepeatedType for confirmation
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false, // Don't map directly to the 'password' property
                'required' => false, // IMPORTANT: Make password change optional
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    // Only apply Length constraint if a value is actually entered
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096, // Max length allowed by Symfony
                        // Apply this constraint only if the field is not blank
                        'groups' => ['OptionalPassword'], // Use a validation group (optional but clean)
                    ]),
                    // Alternatively, handle this logic in the controller or use a callback constraint
                ],
                'first_options' => [
                    'label' => 'New Password (optional)',
                    'help' => 'Leave blank to keep your current password.',
                    'attr' => ['class' => 'form-control mb-1']
                ],
                'second_options' => [
                    'label' => 'Repeat New Password',
                    'attr' => ['class' => 'form-control']
                ],
                'invalid_message' => 'The password fields must match.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            // Add novalidate to disable HTML5 validation, rely on Assertions
            'attr' => ['novalidate' => 'novalidate'],
            // Define validation groups if using them for optional password
            'validation_groups' => function ($form) {
                $data = $form->getData();
                // If plainPassword field has data, validate using 'OptionalPassword' group PLUS the 'Default' group
                if ($form->get('plainPassword')->getData()) {
                    return ['Default', 'OptionalPassword'];
                }
                // Otherwise, just use the default validation group (validates username, email etc.)
                return ['Default'];
            },
        ]);
    }
}