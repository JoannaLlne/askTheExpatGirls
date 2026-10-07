<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use App\Enum\AppointmentStatus;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class AppointmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Appointment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->hideOnForm();
        yield AssociationField::new('application', 'Application');
        yield DateTimeField::new('date', 'Date');

        yield ChoiceField::new('status', 'Status')
            ->setChoices(array_combine(
                array_map(fn ($case) => $case->value, AppointmentStatus::cases()),
                AppointmentStatus::cases()
            ));
    }
}
