<?php
// src/Form/ReclamationType.php
namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user']; // Get the user object passed in options

        $builder
            ->setMethod('POST') // Good practice to be explicit
            ->add('sujet', TextType::class, [
                'label' => 'Subject',
                // 'required' => true is implied by NotBlank on entity
                'attr' => ['class' => 'form-control'] // Add standard styling
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                // 'required' => true is implied by NotBlank on entity
                'attr' => ['class' => 'form-control', 'rows' => 5] // Add standard styling & rows
            ])

            // --- Fields displayed but not submitted (mapped=false) ---
            // These rely on the $user option passed to the form
            ->add('email', EmailType::class, [
                'mapped' => false, // Not mapped to Reclamation entity's email field
                'data' => $user->getEmail(), // Pre-fill from user object
                'disabled' => true, // Make read-only
                'label' => 'Your Email',
                'attr' => ['class' => 'form-control-plaintext'] // Style as plain text
            ])
            ->add('username', TextType::class, [ // Assuming you have a username/name method
                'mapped' => false,
                'data' => method_exists($user, 'getUsername') ? $user->getUsername() : (method_exists($user, 'getNom') ? $user->getNom() : 'N/A'),
                'disabled' => true,
                'label' => 'Your Username/Name',
                'attr' => ['class' => 'form-control-plaintext']
            ])
            // ->add('role', TextType::class, [ // Role display might not be necessary on submission form
            //     'mapped' => false,
            //     'data' => implode(', ', $user->getRoles()),
            //     'disabled' => true,
            //     'label' => 'Your Role',
            //     'attr' => ['class' => 'form-control-plaintext']
            // ])
            // Removed 'date' field - it's set by the entity constructor
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
            // This attribute tells the browser not to use its built-in validation.
            // Symfony's server-side validation (using Entity Assertions) will still run.
            'attr' => ['novalidate' => 'novalidate'],
        ]);
        // Ensure the 'user' option is required when creating this form type
        $resolver->setRequired('user');
        // Define allowed types for the 'user' option (replace with your actual User class)
        // $resolver->setAllowedTypes('user', \App\Entity\User::class); // Or your UserInterface
    }
}