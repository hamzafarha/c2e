<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use App\Entity\Equipement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipementForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
           ->add('typeeq', ChoiceType::class, [
        'label' => 'Type d’équipement',
        'choices' => [
            'Informatique' => 'informatique',
            'Réseau' => 'réseau',
            'PC' => 'PC',
            'Imprimantes' => 'Imprimantes',
            'Autres' => 'autres',
        ],
        'placeholder' => 'Sélectionnez un type...',
        'attr' => ['class' => 'form-select'], // Bootstrap style
    ])
            ->add('nomeq')
            ->add('referenceeq')
            ->add('localisationeq')
            ->add('modeleeq')
            ->add('numserieeq')
                ->add('etat', ChoiceType::class, [
        'label' => 'État',
        'choices' => [
            'En service' => 'en_service',
            'En panne' => 'en_panne',
            'Maintenance préventive' => 'maintenance',
            'Hors service' => 'hors_service',
        ],
        'placeholder' => 'Sélectionner un état',
        'attr' => ['class' => 'form-select']
    ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipement::class,
        ]);
    }
}
