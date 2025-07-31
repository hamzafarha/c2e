<?php

namespace App\Form;

use App\Entity\Equipement;
use App\Entity\Intervention;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;



class InterventionForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateint')
           ->add('typeint', ChoiceType::class, [
            'label' => 'Type d’intervention',
            'choices' => [
                'Préventif' => 'préventif',
                'Curatif' => 'curatif',
            ],
            'placeholder' => 'Choisir un type',
            'attr' => ['class' => 'form-select', 'id' => 'typeint-select']
        ])
            ->add('technicien')
            ->add('etatapres', ChoiceType::class, [
                'label' => 'État après',
                'choices' => [
                    'En service' => 'en service',
                    'En panne' => 'en panne',
                    'Hors service' => 'hors service',
                ],
                'placeholder' => 'Choisir un état',
                'attr' => ['class' => 'form-select'],
            ])
           ->add('prochainedate', DateType::class, [
    'widget' => 'single_text',
    'label' => 'Prochaine intervention',
    'attr' => ['class' => 'form-control', 'id' => 'prochaine-date-field']
])
    
       ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
        ]);
    }
}
