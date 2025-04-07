<?php
                    // src/Form/ReclamationType.php

                    namespace App\Form;

                    use App\Entity\Reclamation;
                    use Symfony\Component\Form\AbstractType;
                    use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
                    use Symfony\Component\Form\Extension\Core\Type\EmailType;
                    use Symfony\Component\Form\Extension\Core\Type\TextareaType;
                    use Symfony\Component\Form\Extension\Core\Type\TextType;
                    use Symfony\Component\Form\FormBuilderInterface;
                    use Symfony\Component\OptionsResolver\OptionsResolver;

                    class ReclamationType extends AbstractType
                    {
                        public function buildForm(FormBuilderInterface $builder, array $options): void
                        {
                            // Get the logged in user data from options.
                            $user = $options['user'];

                            $builder
                                // Editable fields from the entity.
                                ->add('sujet', TextType::class, [
                                    'label' => 'Subject',
                                ])
                                ->add('description', TextareaType::class, [
                                    'label' => 'Description',
                                ])
                                ->add('date', DateTimeType::class, [
                                    'label' => 'Date',
                                    'widget' => 'single_text',
                                ])
                                // Display user info (not mapped).
                                ->add('email', EmailType::class, [
                                    'mapped' => false,
                                    'data' => $user->getEmail(),
                                    'disabled' => true,
                                    'label' => 'Email',
                                ])
                                ->add('username', TextType::class, [
                                    'mapped' => false,
                                    'data' => method_exists($user, 'getUsername') ? $user->getUsername() : $user->getNom(),
                                    'disabled' => true,
                                    'label' => 'Username',
                                ])
                                ->add('role', TextType::class, [
                                    'mapped' => false,
                                    'data' => implode(', ', $user->getRoles()),
                                    'disabled' => true,
                                    'label' => 'Role',
                                ])
                            ;
                        }

                        public function configureOptions(OptionsResolver $resolver): void
                        {
                            $resolver->setDefaults([
                                'data_class' => Reclamation::class,
                            ]);

                            // Make sure 'user' is passed when building the form.
                            $resolver->setRequired('user');
                        }
                    }