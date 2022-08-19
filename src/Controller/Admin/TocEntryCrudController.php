<?php

namespace App\Controller\Admin;

use App\Entity\TocEntry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

class TocEntryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TocEntry::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('work'),
            Field::new('label'),
            Field::new('sortOrder'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('work')
            ;
    }
}
