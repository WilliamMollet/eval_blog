<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Utilisateur;
use App\Entity\Categorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('contenu')
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'titre'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Publier',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->add('draft', SubmitType::class, [
                'label' => 'Brouillon',
                'attr' => [
                    'class' => 'btn btn-secondary'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
