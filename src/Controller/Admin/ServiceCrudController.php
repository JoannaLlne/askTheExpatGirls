<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use App\Enum\ServiceCategory;
use App\Enum\ServiceTiming;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\Validator\Constraints\Image;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

class ServiceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Service::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->hideOnForm();
        yield TextField::new('name', 'Name');
        yield TextEditorField::new('description', 'Description');
        yield IntegerField::new('price', 'Price');

        yield ChoiceField::new('serviceCategory', 'Service Category')
            ->setChoices(array_combine(
                array_map(fn (ServiceCategory $c) => $c->label(), ServiceCategory::cases()),
                array_map(fn (ServiceCategory $c) => $c->value, ServiceCategory::cases())
            ))
            ->allowMultipleChoices();

        yield ChoiceField::new('serviceTiming', 'Service Timing')
            ->setChoices(array_combine(
                array_map(fn (ServiceTiming $t) => $t->label(), ServiceTiming::cases()),
                array_map(fn (ServiceTiming $t) => $t->value, ServiceTiming::cases())
            ))
            ->allowMultipleChoices();

        yield ImageField::new('image', 'Photo')
            ->setBasePath('/uploads/services')
            ->setUploadDir('public/uploads/services')
            ->setRequired(false)
            //Pour ne pas converser le nom original de la photo
            ->setUploadedFileNamePattern('[uuid58].[extension]')
            ->setFileConstraints(
            new Image(maxSize: '3M', maxWidth: 3000,
                maxHeight: 3000, extensions: ['jpg', 'jpeg', 'png', 'webp'],)
            );

        yield DateTimeField::new('createdAt', 'Created')
            ->hideOnForm();
    }
}
