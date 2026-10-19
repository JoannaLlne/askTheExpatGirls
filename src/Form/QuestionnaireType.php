<?php

namespace App\Form;

use App\Entity\Questionnaire;
use App\Enum\ServiceCategory;
use App\Enum\Situation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionnaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $services = $options['services'];
        $packages = $options['packages'];

        $choices = [];
        foreach ($services as $service) {
            $choices[$service->getName()] = 'service-' . $service->getId();
        }
        foreach ($packages as $package) {
            $choices[$package->getName()] = 'package-' . $package->getId();
        }

        $builder
            ->add('firstName', TextType::class, [
                'label' => 'First name',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last name',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email address',
            ])
            ->add('newsletter', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'I want to subscribe to the newsletter',
            ])
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
            ->add('serviceOrPackage', ChoiceType::class, [
                'choices' => $choices,
                'mapped' => false,
                'label' => 'Which service or package do you need?',
                'placeholder' => 'Choose an option',
            ])
            ->add('additionalInfo', TextareaType::class, [
                'label' => 'Additional Info',
                'required'   => false,
                'attr' => [
                    'rows' => 10,
                    'cols'=>50,
                ]
            ])
            //Anti spam
            ->add('website', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Questionnaire::class,
            'services' => [],
            'packages' => [],
        ]);
    }
}
