<?php

namespace App\Form;

use App\Entity\Post;
// Removed User EntityType as author will be set automatically
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Post Title',
                'attr' => ['class' => 'form-control'] // Add your project's input class
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Post Content',
                'attr' => ['class' => 'form-control', 'rows' => 8] // Add class and suggest rows
            ])
            // Do NOT add 'createdAt', 'updatedAt', 'author' here - handled in Controller/Entity
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}