<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Sortiestock;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortiestockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('idart', EntityType::class, [
                'class' => Article::class,
                'choice_label' => function (Article $article) {
                    return $article->getRefart() . ' - ' . $article->getNomart();
                },
                'label' => 'Article',
                'placeholder' => 'Sélectionnez un article...',
                'attr' => ['class' => 'form-select']
            ])
            ->add('datesortie', DateType::class, [
                'label' => 'Date de sortie',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'date'
                ]
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Quantité sortie du stock',
                    'min' => 1
                ]
            ])
            ->add('technicien', TextType::class, [
                'label' => 'Technicien',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom du technicien'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortiestock::class,
        ]);
    }
}
