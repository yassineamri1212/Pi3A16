<?php

                namespace App\Form;

                use App\Entity\Livraison;
                use Symfony\Component\Form\AbstractType;
                use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
                use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
                            ->add('packages', CollectionType::class, [
                                'entry_type' => PackageType::class,
                                'label' => 'Packages in this Delivery',
                                'allow_add' => true,
                                'allow_delete' => true,
                                'by_reference' => false,
                                'attr' => [
                                    'class' => 'packages-collection',
                                    'data-controller' => 'form-collection',
                                    'data-form-collection-add-label-value' => 'Add Package',
                                    'data-form-collection-delete-label-value' => 'Remove Package',
                                ],
                            ])
                        ;
                    }

                    public function configureOptions(OptionsResolver $resolver): void
                    {
                        $resolver->setDefaults([
                            'data_class' => Livraison::class,
                        ]);
                    }
                }