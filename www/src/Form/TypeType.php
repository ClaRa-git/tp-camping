<?php

namespace App\Form;

use App\Entity\Price;
use App\Entity\Type;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class TypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Nom du type',
                ],
            ])
            ->add('price', IntegerType::class, [
                'label' => 'Prix',
                'attr' => [
                    'placeholder' => 'Prix du type',
                ],
            ])
        ;

        // Si on est en mode création, on ajoute le champ image et on le rend obligatoire
        if (!$options['is_edit']) {
            $builder
                ->add('imagePath', FileType::class, [
                    'label' => 'Image de la série',
                    'mapped' => false,
                    'required' => true,
                    'constraints' => [
                        new File([
                            'maxSize' => '5000k',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                                'image/jpg',
                                'image/gif',
                                'image/webp'
                            ],
                            'mimeTypesMessage' => 'Merci de choisir un format d\'image valide (jpeg, jpg, png, gif, webp)',
                        ])
                    ],
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]);
        }

        // Si on est en mode édition, pour ne pas avoir à recharger 
        // l'image à chaque fois si on ne veut pas la changer
        // Le champ imagePath n'est pas obligatoire et on ajoute un 
        // champ caché pour stocker le nom de l'image actuelle
        if ($options['is_edit']) {
            $builder->add('imagePath', FileType::class, [
                'label' => 'Image du type de bien',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5000k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                            'image/gif',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Merci de choisir un format d\'image valide (jpeg, jpg, png, gif, webp)',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('currentImage', HiddenType::class, [
                'mapped' => false
            ]);
        }
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Type::class,
            'is_edit' => false
        ]);
    }
}
