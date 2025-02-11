<?php

namespace App\Form;

use App\Entity\Season;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeasonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Nom de la saison',
                'attr' => [
                    'placeholder' => 'Nom de la saison',
                    'class' => 'form-control',
                ],
            ])
            ->add('isClosed', ChoiceType::class, [
                'label' => 'Camping fermé ?',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => false,
                'multiple' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('percentage', IntegerType::class, [
                'label' => 'percentage de réduction / augmentation',
                'attr' => [
                    'min' => 0,
                    'max' => 200,
                    'step' => 1,
                    'placeholder' => 'percentage',
                    'class' => 'form-control',
                ],
            ])
            ->add('dateStart', DateTimeType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => [
                    'placeholder' => 'Date de début',
                    'class' => 'form-control',
                ],
            ])
            ->add('dateEnd', DateTimeType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr' => [
                    'placeholder' => 'Date de fin',
                    'class' => 'form-control',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Season::class,
        ]);
    }
}
