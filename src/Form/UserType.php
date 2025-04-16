<?php
                namespace App\Form;

                use App\Entity\User;
                use Symfony\Component\Form\AbstractType;
                use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
                use Symfony\Component\Form\Extension\Core\Type\EmailType;
                use Symfony\Component\Form\Extension\Core\Type\PasswordType;
                use Symfony\Component\Form\Extension\Core\Type\TextType;
                use Symfony\Component\Form\FormBuilderInterface;
                use Symfony\Component\OptionsResolver\OptionsResolver;
                use Symfony\Component\Validator\Constraints\Length;
                use Symfony\Component\Validator\Constraints\NotBlank;

                class UserType extends AbstractType
                {
                    public function buildForm(FormBuilderInterface $builder, array $options): void
                    {
                         $isNew = ($options['data'] && null === $options['data']->getId());
                         $builder
                              ->add('email', EmailType::class, [
                                   'label' => 'Email Address',
                                   'attr' => ['class' => 'form-control']
                              ])
                              ->add('userName', TextType::class, [
                                   'label' => 'Username',
                                   'attr' => ['class' => 'form-control']
                              ])
                              ->add('plainPassword', PasswordType::class, [
                                   'label' => $isNew ? 'Password' : 'New Password (optional)',
                                   'help' => $isNew ? '' : 'Leave blank to keep current password.',
                                   'mapped' => false,
                                   'required' => $isNew,
                                   'attr' => [
                                        'autocomplete' => 'new-password',
                                        'class' => 'form-control'
                                   ],
                                   'constraints' => [
                                        ...($isNew ? [new NotBlank(['message' => 'Please enter a password.'])] : []),
                                        new Length([
                                             'min' => 6,
                                             'minMessage' => 'Password should be at least {{ limit }} characters',
                                             'max' => 4096,
                                        ]),
                                   ],
                              ])
                              ->add('roles', ChoiceType::class, [
                                   'label' => 'Roles',
                                   'choices' => [
                                        'Standard User' => 'ROLE_USER',
                                       'Conducteur (Driver)' => 'ROLE_CONDUCTEUR', // <<< ADD THIS OPTION

                                       'Administrator' => 'ROLE_ADMIN'
                                   ],
                                   'multiple' => true,
                                   'expanded' => true,
                                   'label_attr' => ['class' => 'checkbox-inline'],
                                   'attr' => ['class' => 'form-check-input-group'],
                              ]);
                    }

                    public function configureOptions(OptionsResolver $resolver): void
                    {
                         $resolver->setDefaults([
                             'data_class' => User::class,
                             'attr' => ['novalidate' => 'novalidate'],
                         ]);
                    }
                }