<?php

namespace App\Controller\Admin;

use App\Entity\Questionnaire;
use App\Enum\ApplicationStatus;
use App\Enum\ServiceCategory;
use App\Enum\Situation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class QuestionnaireCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Questionnaire::class;
    }


    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID');
        yield TextField::new('firstName', 'First name');
        yield TextField::new('lastName', 'Last name');
        yield TextField::new('nationality', 'Nationality');

        yield TextField::new('situationLabel', 'Situation');
        yield TextField::new('helpReasonLabel', 'Help reason');

        yield TextField::new('additionalInfo', 'Additional info');

        yield ChoiceField::new('applicationStatus', 'Application status')
            ->setChoices(array_combine(
                array_map(fn (ApplicationStatus $c) => $c->label(), ApplicationStatus::cases()),
                ApplicationStatus::cases()
            ));
    }

}
