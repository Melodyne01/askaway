<?php

namespace App\Form;

use App\Repository\DomainRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchDomainType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', EntityType::class, [
            'class' => Categorie::class,
            'query_builder' => function (DomainRepository $repo) {
                return $repo->createQueryBuilder('d')
                ->orderBy('d.name', 'ASC');;
            },
            'required' => true,
            'label' => false
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
