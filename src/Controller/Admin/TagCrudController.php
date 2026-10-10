<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Tag;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TagCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tag::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Tag')
            ->setEntityLabelInPlural('Tags')
            ->setSearchFields(['name'])
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield ImageField::new('image', 'Icon')
            ->setBasePath(Tag::IMAGE_BASE_PATH)
            ->setUploadDir(Tag::IMAGE_UPLOAD_DIR)
            ->setUploadedFileNamePattern('[slug]-[contenthash].[extension]')
            // The default Image constraint rejects vector files, so validate by MIME type instead.
            ->setFileConstraints([])
            ->mimeTypes('image/svg+xml,.svg', 'Please upload an SVG file.')
            ->maxSize('512k')
            ->setHelp('SVG only, up to 512 KB.');
        yield TextField::new('name');
        yield AssociationField::new('posts')
            ->autocomplete()
            // Post owns the relation: make the form call addPost()/removePost() so changes get persisted.
            ->setFormTypeOption('by_reference', false);
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
    }
}
