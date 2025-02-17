<?php

namespace App\Form;

use App\Entity\Equipment;
use App\Entity\Rental;
use Faker\Provider\ar_JO\Text;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Nom de l\'équipement',
                'attr' => [
                    'placeholder' => 'Nom de l\'équipement'
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
            'data_class' => Equipment::class,
        ]);
    }
}
