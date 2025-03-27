<?php
            // src/Form/MoyenDeTransportType.php
            namespace App\Form;

            use App\Entity\MoyenDeTransport;
            use App\Entity\Evenement;
            use Symfony\Component\Form\AbstractType;
            use Symfony\Component\Form\FormBuilderInterface;
            use Symfony\Bridge\Doctrine\Form\Type\EntityType;
            use Symfony\Component\OptionsResolver\OptionsResolver;

            class MoyenDeTransportType extends AbstractType
            {
                public function buildForm(FormBuilderInterface $builder, array $options): void
                {
                    $builder
                        ->add('prix')
                        ->add('type')
                        ->add('nbrePlaces')
                        ->add('evenement', EntityType::class, [
                            'class' => Evenement::class,
                            'choice_label' => 'nom',
                            'placeholder' => 'Select an event'
                        ]);
                }

                public function configureOptions(OptionsResolver $resolver): void
                {
                    $resolver->setDefaults([
                        'data_class' => MoyenDeTransport::class,
                    ]);
                }
            }