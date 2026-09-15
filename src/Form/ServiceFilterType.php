<?php

namespace App\Form;

use App\Dto\ServiceFilterDto;
use App\Entity\Service;
use App\Enum\ServiceCategory;
use App\Enum\ServiceTiming;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('timing', EnumType::class, [
                'class' => ServiceTiming::class,
                'choice_label' => fn(ServiceTiming $t) => $t->label(),
                'required' => false,
                'placeholder' => 'All types',
                'expanded' => false, // select
                'multiple' => false,
            ])
            ->add('categories', EnumType::class, [
                'class' => ServiceCategory::class,
                'choice_label' => fn(ServiceCategory $c) => $c->label(),
                'required' => false,
                'expanded' => true,  // checkboxes
                'multiple' => true,
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'data_class' => ServiceFilterDto::class,
            'csrf_protection' => false,
        ]);
    }
}
