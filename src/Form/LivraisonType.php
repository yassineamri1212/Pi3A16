<?php

namespace App\Form;

use App\Entity\Livraison;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType; // Import CollectionType
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LivraisonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startLocation', TextType::class, [
                'label' => 'Start Location / Pickup Point',
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., Warehouse A, Tunis']
            ])
            ->add('deliveryLocation', TextType::class, [
                'label' => 'Delivery Location / Destination',
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., Customer Address, Sousse']
            ])
            ->add('isDelivered', CheckboxType::class, [
                'label' => 'Mark as Delivered?',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            // --- Embed the Packages Form Collection ---
            ->add('packages', CollectionType::class, [
                // Specify the form type for each item in the collection
                'entry_type' => PackageType::class,
                // Define options for the embedded PackageType form
                // 'standalone' is false by default in PackageType, so we don't strictly need to pass it
                // 'entry_options' => ['label' => false], // Example: hide labels of embedded forms if desired

                'label' => 'Packages in this Delivery', // Label for the whole collection

                // Allow adding new package entries via JavaScript
                'allow_add' => true,
                // Allow removing existing package entries via JavaScript
                'allow_delete' => true,

                // Tell Symfony to add/remove Packages directly on the Livraison object
                'by_reference' => false,

                // Attributes for the wrapping element (e.g., a div around the collection)
                'attr' => [
                    'class' => 'packages-collection', // Add a class for JS targeting
                    // Data attributes needed for the Stimulus controller (or custom JS)
                    'data-controller' => 'form-collection', // Connect to Stimulus controller
                    'data-form-collection-add-label-value' => 'Add Package', // Text for the "Add" button
                    'data-form-collection-delete-label-value' => 'Remove Package', // Text for the "Remove" button
                ],
            ])
            // --- End Packages Collection ---
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Livraison::class,
        ]);
    }
}