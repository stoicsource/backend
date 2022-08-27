<?php

namespace App\Controller\Admin;

use App\Entity\Edition;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

class EditionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Edition::class;
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
            Field::new('year'),
            Field::new('name'),
            AssociationField::new('author'),
            Field::new('language'),
            Field::new('quality'),
            Field::new('source')->hideOnIndex(),
            // Field::new('contributor')->hideOnIndex() <- json
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('work')
            ->add('author')
            ;
    }
}
