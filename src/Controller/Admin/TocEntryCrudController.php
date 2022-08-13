<?php

namespace App\Controller\Admin;

use App\Entity\TocEntry;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TocEntryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TocEntry::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
