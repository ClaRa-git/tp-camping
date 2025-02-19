<?php

namespace App\Form;

use App\Entity\Equipment;
use App\Entity\Rental;
use App\Entity\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RentalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Titre de la location',
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Description de la location',
                ]
            ])
            ->add('bedding', IntegerType::class, [
                'label' => 'Nombre de lits',
                'attr' => [
                    'placeholder' => 'Nombre de lits',
                ]
            ])
            ->add('surface', IntegerType::class, [
                'label' => 'Surface',
                'attr' => [
                    'placeholder' => 'Surface en m²',
                ]
            ])
            ->add('location', IntegerType::class, [
                'label' => 'Numéro de l\'emplacement',
                'attr' => [
                    'placeholder' => 'Numéro de l\'emplacement',
                ]
            ])
            ->add('isClean', ChoiceType::class, [
                'label' => 'Location propre ?',
                'choices' => [
                    'Oui' => true,
                    'Non' => false
                ],
                'expanded' => false,
                'multiple' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('type', EntityType::class, [
                'class' => Type::class,
                'choice_label' => 'label',
                'label' => 'Type de logement',
                'attr' => [
                    'placeholder' => 'Type de logement',
                    'class' => 'form-control'
                ],
                'multiple' => false,
                'expanded' => false,
            ])
            ->add('equipments', EntityType::class, [
                'class' => Equipment::class,
                'choice_label' => 'label',
                'label' => 'Équipements',
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'placeholder' => 'Équipements',
                    'class' => 'form-control'
                ]
            ])
            ->add('isActive', ChoiceType::class, [
                'label' => 'Location active ?',
                'choices' => [
                    'Oui' => true,
                    'Non' => false
                ],
                'expanded' => false,
                'multiple' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rental::class,
        ]);
    }
}
