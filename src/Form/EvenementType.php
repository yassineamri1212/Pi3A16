<?php
// src/Form/EvenementType.php
namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType; // Use DateTimeType if you need time
use Symfony\Component\Form\Extension\Core\Type\DateType; // Or DateType for just the date
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Vich\UploaderBundle\Form\Type\VichImageType;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Event Name', // Add labels here or in Twig
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false, // Example: make description optional
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Location',
                'required' => false,
            ])
            ->add('date_evenement', DateType::class, [ // Or DateTimeType if time is needed
                'label' => 'Event Date',
                'widget' => 'single_text', // Good for HTML5 date pickers
                'html5' => true,
                'constraints' => [
                    new GreaterThanOrEqual([
                        // Use 'today' string for comparison, it's more robust
                        'value'   => 'today',
                        'message' => 'The event date cannot be in the past.',
                    ]),
                ],
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'Event Image (Upload new)', // Clearer label
                'required' => false, // IMPORTANT: Allows editing without re-uploading
                'allow_delete' => true, // Allows user to check a box to remove the image
                'delete_label' => 'Remove current image?', // Label for the delete checkbox
                'download_uri' => false, // Typically don't need a download link here
                'image_uri' => false, // Don't generate an automatic image URI, we'll display it manually
                'asset_helper' => true, // IMPORTANT: Use the asset helper for generating paths (works with vich_uploader_asset)
                // Add constraints if needed (handled by Entity annotation usually, but can be duplicated here)
                // 'constraints' => [
                //     new Image(['maxSize' => '2M', 'mimeTypes' => ['image/jpeg', 'image/png']])
                // ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}