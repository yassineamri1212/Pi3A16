<?php

namespace App\Form;

use App\Entity\Livraison; // Import Livraison
use App\Entity\Package;
use Symfony\Bridge\Doctrine\Form\Type\EntityType; // To select Livraison (if needed standalone)
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType; // Use Textarea for description
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PackageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('weightPackage', IntegerType::class, [
                'label' => 'Weight (grams)', // Specify unit
                'attr' => ['class' => 'form-control', 'min' => 1, 'placeholder' => 'e.g., 500']
            ])
            ->add('descriptionPackage', TextareaType::class, [ // Use Textarea for potentially longer descriptions
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 3] // Adjust rows as needed
            ]);

        // Only add the 'livraison' field if this form is used *standalone*
        // If it's embedded in LivraisonType, the relationship is handled automatically.
        if ($options['standalone']) {
            $builder->add('livraison', EntityType::class, [
                'class' => Livraison::class,
                //'choice_label' => '__toString', // Use the __toString method from Livraison
                'placeholder' => '--- Select Delivery ---',
                'attr' => ['class' => 'form-select']
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Package::class,
            'standalone' => false, // Default to not being standalone (i.e., embedded)
        ]);
        // Allow the 'standalone' option to be passed when creating the form
        $resolver->setAllowedTypes('standalone', 'bool');
    }
}