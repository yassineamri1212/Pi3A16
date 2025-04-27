<?php

namespace App\Form;

use App\Entity\Commentaire;
use App\Entity\Post; // Needed if using EntityType for Post
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Comment',
                'attr' => ['class' => 'form-control', 'rows' => 4]
            ])
            // Only add 'post' if managing comments globally and need to select one.
            // Typically, you add comments FROM a specific post's page,
            // so the post is set in the controller, not selected here.
            // If needed:
            // ->add('post', EntityType::class, [
            //     'class' => Post::class,
            //     'choice_label' => 'title',
            //     'placeholder' => 'Select Post',
            //     'attr' => ['class' => 'form-select']
            // ])
            // Do NOT add 'createdAt', 'author' here
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
        ]);
    }
}