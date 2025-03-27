<?php
                     // src/Form/UserType.php

                     namespace App\Form;

                     use App\Entity\User;
                     use Symfony\Component\Form\AbstractType;
                     use Symfony\Component\Form\FormBuilderInterface;
                     use Symfony\Component\OptionsResolver\OptionsResolver;
                     use Symfony\Component\Form\Extension\Core\Type\TextType;
                     use Symfony\Component\Form\Extension\Core\Type\PasswordType;
                     use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

                     class UserType extends AbstractType
                     {
                         public function buildForm(FormBuilderInterface $builder, array $options)
                         {
                             $builder
                                 ->add('email', TextType::class)
                                 ->add('userName', TextType::class)
                                 // Add plainPassword field so that it exists in the form.
                                 ->add('plainPassword', PasswordType::class, [
                                     'mapped' => false,
                                     'required' => true,
                                     'label' => 'Password',
                                     'attr' => ['autocomplete' => 'new-password'],
                                 ])
                                 ->add('roles', ChoiceType::class, [
                                     'choices' => [
                                         'User' => 'ROLE_USER',
                                         'Admin' => 'ROLE_ADMIN'
                                     ],
                                     'multiple' => true,
                                     'expanded' => true,
                                 ])
                             ;
                         }

                         public function configureOptions(OptionsResolver $resolver)
                         {
                             $resolver->setDefaults([
                                 'data_class' => User::class,
                             ]);
                         }
                     }