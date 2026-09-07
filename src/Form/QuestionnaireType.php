<?php

namespace App\Form;

use App\Entity\Questionnaire;
use App\Entity\User;
use App\Enum\ServiceCategory;
use App\Enum\Situation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionnaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nationality', TextType::class, [
                'label' => 'Nationality',
            ])
            ->add('situation', EnumType::class, [
                'class' => Situation::class,
                'label' => 'Your situation',
                'choice_label'=> fn (Situation $situation) => $situation->label(),

            ])
            ->add('helpReason', EnumType::class, [
                'class'=> ServiceCategory::class,
                'label' => 'Help Reason',
                'choice_label'=> fn (ServiceCategory $serviceCategory) => $serviceCategory->label(),
            ])
            ->add('additionalInfo', TextareaType::class, [
                'label' => 'Additional Info',
                'required'   => false,
                'attr' => [
                    'rows' => 10,
                    'cols'=>50,
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Questionnaire::class,
        ]);
    }
}
