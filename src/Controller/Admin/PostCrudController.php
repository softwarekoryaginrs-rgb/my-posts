<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Post;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NullFilter;

class PostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Post')
            ->setEntityLabelInPlural('Posts')
            ->setSearchFields(['title', 'shortTitle', 'shortDescription'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle(Crud::PAGE_DETAIL, static fn (Post $post): string => (string) $post->getTitle());
    }

    public function configureActions(Actions $actions): Actions
    {
        // Opens the post as the site renders it, including drafts and deleted posts.
        $preview = Action::new('preview', 'Preview', 'fa fa-eye')
            ->linkToUrl(fn (Post $post): string => $this->generateUrl('app_post_preview', ['id' => $post->getId()]))
            ->setHtmlAttributes(['target' => '_blank', 'rel' => 'noopener']);

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $preview)
            ->add(Crud::PAGE_DETAIL, $preview)
            ->add(Crud::PAGE_EDIT, $preview);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('published', 'Shown on site'))
            ->add(EntityFilter::new('tags'))
            ->add(DateTimeFilter::new('createdAt'))
            ->add(NullFilter::new('deletedAt')->setChoiceLabels('Active', 'Deleted'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('Content');
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title');
        yield TextField::new('shortTitle');
        yield BooleanField::new('published', 'Show on site')
            ->setHelp('Unpublished posts are hidden from the site but can still be opened via Preview.');
        yield TextareaField::new('shortDescription')->hideOnIndex();
        yield AssociationField::new('tags')->autocomplete();
        yield CodeEditorField::new('text')
            ->setLanguage('markdown')
            ->setNumOfRows(30)
            ->setHelp('Markdown (GitHub flavored: tables, task lists, ~~strikethrough~~, fenced code).')
            ->onlyOnForms();
        yield TextField::new('text', 'Preview')
            ->setTemplatePath('admin/field/markdown.html.twig')
            ->onlyOnDetail();

        yield FormField::addTab('SEO')
            ->setHelp(SeoTemplateCrudController::placeholdersHelp());
        yield AssociationField::new('seoTemplate', 'SEO template')
            ->setHelp('Leave empty to use the default template.')
            ->hideOnIndex();
        yield TextField::new('seoTitle', 'Title override')
            ->setHelp('Replaces the template title for this post. Leave empty to use the template.')
            ->hideOnIndex();
        yield TextareaField::new('seoDescription', 'Description override')
            ->setHelp('Replaces the template description for this post. Leave empty to use the template.')
            ->hideOnIndex();

        yield FormField::addTab('Meta');
        yield IntegerField::new('viewsCount');
        yield DateTimeField::new('createdAt');
        // Refreshed automatically on every save, so it is shown read-only.
        yield DateTimeField::new('updatedAt')->setDisabled()->hideOnIndex();
        yield DateTimeField::new('deletedAt')->setHelp('Set to mark the post as deleted.');
    }
}
