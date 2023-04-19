<?php

namespace App\Form;

use App\Entity\Manuel;
use App\Entity\Pays;
use App\Entity\Produit;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreatProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('denomination',   TextType::class,
                [
                    'label' => 'Nom du produit : ',
                    'attr' => ['placeholder' => 'votre créations'],
                ])
            ->add('code',  IntegerType::class,
                ['label' => 'code barre'])

            ->add('dateCreation', DateTimeType::class, [
                'label' => 'Date de création : ',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd HH:mm:ss',
                'html5' => false,
                'attr' => [
                    'class' => 'datepicker', // ajout d'une classe CSS
                    'autocomplete' => 'on', // activation de l'autocomplétion du navigateur
                ],
                    'data' => new \DateTime(), // valeur par défaut
            ])
            ->add('actif', ChoiceType::class, [
                'choices' => [
                    'Inactif' => 0,
                    'Actif' => 1,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('descriptif', TextareaType::class, [

                'required' => false,
                'mapped' => true,
                'label' => 'Description : ',
                'empty_data' => '',
                'attr' => [
                    'maxlength' => 500
                ],
            ])
            ->add('manuel',
                EntityType::class,
                [
                    'class' => Manuel::class,
                    'choice_label' => function(Manuel $manuel) {
                        return
                            $manuel->getSommaire()
                            . ' ('
                            . (is_null($manuel->getId()) ? '??' : $manuel->getId())
                            . ')';
                    },
                    'placeholder' => '----------',
                    'label' => 'Manuel :',
                    'required' => false,
                    'mapped' => false,
                ])
            ->add('pays',
                EntityType::class,
                [
                    'class' => Pays::class,
                    'choice_label' => function(Pays $pays) {
                        return
                            $pays->getNom()
                            . ' ('
                            . (is_null($pays->getCode()) ? '??' : $pays->getCode())
                            . ')';
                    },
                    'placeholder' => '----------',
                    'label' => 'Pays :',
                    'required' => false,
                    'mapped' => false,
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
