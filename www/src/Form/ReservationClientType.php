<?php

namespace App\Form;

use App\Entity\Rental;
use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateStart', DateTimeType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('dateEnd', DateTimeType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('adultsNumber', IntegerType::class, [
                'label' => 'Nombre d\'adultes',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('kidsNumber', IntegerType::class, [
                'label' => 'Nombre d\'enfants',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('price', HiddenType::class, [
                'mapped' => false, // Ce champ ne sera pas stocké en base avant validation finale
            ])
            ->add('calculate', SubmitType::class, [
                'label' => 'Calculer le prix',
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->add('confirm', SubmitType::class, [
                'label' => 'Confirmer la réservation',
                'attr' => ['class' => 'btn btn-success']
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class
        ]);
    }
}
