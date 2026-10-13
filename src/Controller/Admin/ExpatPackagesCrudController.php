<?php

namespace App\Controller\Admin;

use App\Entity\ExpatPackages;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Validator\Constraints\Image;

class ExpatPackagesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ExpatPackages::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->hideOnForm();
        yield TextField::new('name', 'Name');
        yield TextEditorField::new('description', 'Description');
        yield IntegerField::new('price', 'Price');
        yield BooleanField::new('archived', 'Archived')->renderAsSwitch(true);
        yield ImageField::new('image', 'Photo')
            ->setBasePath('/uploads/packages')
            ->setUploadDir('public/uploads/packages')
            ->setRequired(false)
            //Pour ne pas converser le nom original de la photo
            ->setUploadedFileNamePattern('[uuid58].[extension]')
            ->setFileConstraints(
                new Image(maxSize: '3M', maxWidth: 3000,
                    maxHeight: 3000, extensions: ['jpg', 'jpeg', 'png', 'webp'],)
            );
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->displayIf(
                    static fn (ExpatPackages $package) => $package->getApplications()->isEmpty()
                );
            })
            ->add(Crud::PAGE_EDIT, Action::DELETE)
            ->update(Crud::PAGE_EDIT, Action::DELETE, function (Action $action) {
                return $action->displayIf(
                    static fn (ExpatPackages $package) => $package->getApplications()->isEmpty()
                );
            })
            ->disable(Action::BATCH_DELETE);
    }
}
