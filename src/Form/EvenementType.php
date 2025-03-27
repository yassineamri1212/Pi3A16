<?php
                        namespace App\Form;

                        use App\Entity\Evenement;
                        use Symfony\Component\Form\AbstractType;
                        use Symfony\Component\Form\FormBuilderInterface;
                        use Symfony\Component\OptionsResolver\OptionsResolver;
                        use Symfony\Component\Form\Extension\Core\Type\DateType;
                        use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
                        use Vich\UploaderBundle\Form\Type\VichImageType;

                        class EvenementType extends AbstractType
                        {
                            public function buildForm(FormBuilderInterface $builder, array $options): void
                            {
                                $builder
                                    ->add('nom')
                                    ->add('description')
                                    ->add('lieu')
                                    ->add('date_evenement', DateType::class, [
                                        'widget'      => 'single_text',
                                        'html5'       => true,
                                        'constraints' => [
                                            new GreaterThanOrEqual([
                                                'value'   => (new \DateTime())->format('Y-m-d'),
                                                'message' => 'The event date cannot be in the past.',
                                            ]),
                                        ],
                                    ])
                                    ->add('imageFile', VichImageType::class, [
                                        'required'      => false,
                                        'allow_delete'  => true,
                                        'download_uri'  => false,
                                    ]);
                            }

                            public function configureOptions(OptionsResolver $resolver): void
                            {
                                $resolver->setDefaults([
                                    'data_class' => Evenement::class,
                                ]);
                            }
                        }