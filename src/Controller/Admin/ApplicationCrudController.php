<?php

namespace App\Controller\Admin;

use App\Entity\Application;
use App\Enum\ApplicationStatus;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ApplicationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Application::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->hideOnForm();
        yield TextField::new('email', 'Email')->setFormTypeOption('disabled', true);
        yield AssociationField::new('user', 'User')->setFormTypeOption('disabled', true);
        yield AssociationField::new('questionnaire', 'Questionnaire')->setFormTypeOption('disabled', true);

        yield AssociationField::new('service', 'Service');

        yield ChoiceField::new('status', 'Status')
            ->setChoices(array_combine(
                array_map(fn ($case) => $case->value, ApplicationStatus::cases()),
                ApplicationStatus::cases()
            ));

        yield DateTimeField::new('createdAt', 'Created at')->setFormTypeOption('disabled', true);
        yield DateTimeField::new('updatedAt', 'Updated at')->hideOnForm();
    }
}
