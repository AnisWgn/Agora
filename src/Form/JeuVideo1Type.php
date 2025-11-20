<?php

namespace App\Form;

use App\Entity\Genre;
use App\Entity\JeuVideo;
use App\Entity\Marque;
use App\Entity\Pegi;
use App\Entity\Plateforme;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JeuVideo1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('refJeu')
            ->add('nom')
            ->add('PRIX')
            ->add('dateParution')
            ->add('plateforme', EntityType::class, [
                'class' => Plateforme::class,
                'choice_label' => 'id',
            ])
            ->add('pegi', EntityType::class, [
                'class' => Pegi::class,
                'choice_label' => 'id',
            ])
            ->add('genre', EntityType::class, [
                'class' => Genre::class,
                'choice_label' => 'id',
            ])
            ->add('marque', EntityType::class, [
                'class' => Marque::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JeuVideo::class,
        ]);
    }
}
