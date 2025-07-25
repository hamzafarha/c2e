<?php

namespace App\Form;

use App\Entity\BackupLog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class BackupLogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('backupName', TextType::class, [
                'label' => 'Nom de la sauvegarde',
                'attr' => ['placeholder' => 'Ex : Sauvegarde quotidienne BDD'],
                'required' => true
            ])
            ->add('backupType', ChoiceType::class, [
                'label' => 'Type de sauvegarde',
                'choices' => [
                    'Complète' => 'full',
                    'Incrémentielle' => 'incremental',
                    'Différentielle' => 'differential',
                ],
                'required' => true
            ])
            ->add('startTime', DateTimeType::class, [
                'label' => 'Heure de début',
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'datetimepicker'],
                'required' => true
            ])
            ->add('endTime', DateTimeType::class, [
                'label' => 'Heure de fin',
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'datetimepicker'],
                'required' => true
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Succès' => 'completed',
                    'Échec' => 'failed',
                    'Partiel' => 'partial',
                    'En cours' => 'running',
                ],
                'required' => true
            ])
            ->add('totalSizeGB', NumberType::class, [
                'label' => 'Taille (GB)',
                'required' => false
            ])
            ->add('filesProcessed', IntegerType::class, [
                'label' => 'Fichiers traités',
                'required' => false
            ])
            ->add('sourcePath', TextType::class, [
                'label' => 'Chemin source',
                'required' => false
            ])
            ->add('destinationPath', TextType::class, [
                'label' => 'Chemin destination',
                'required' => false
            ])
            ->add('details', TextareaType::class, [
                'label' => 'Détails techniques',
                'required' => false,
                'attr' => ['rows' => 4]
            ])
            ->add('errorsCount', IntegerType::class, [
                'label' => 'Nombre d\'erreurs',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BackupLog::class,
        ]);
    }
} 