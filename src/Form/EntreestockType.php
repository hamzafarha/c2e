<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Entreestock;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntreestockType extends AbstractType
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
            ->add('dateentree', DateType::class, [
                'label' => 'Date d\'entrée',
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
                    'placeholder' => 'Quantité entrée en stock',
                    'min' => 1
                ]
            ])
            ->add('prixu', NumberType::class, [
                'label' => 'Prix unitaire (€)',
                'scale' => 2,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Prix unitaire en euros',
                    'step' => '0.01',
                    'min' => 0
                ]
            ])
            ->add('fournisseur', TextType::class, [
                'label' => 'Fournisseur',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom du fournisseur'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Entreestock::class,
        ]);
    }
}
