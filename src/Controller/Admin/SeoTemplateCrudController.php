<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SeoTemplate;
use App\Repository\SeoTemplateRepository;
use App\Service\Seo\PostSeoResolver;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SeoTemplateCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly SeoTemplateRepository $seoTemplateRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return SeoTemplate::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('SEO template')
            ->setEntityLabelInPlural('SEO templates')
            ->setSearchFields(['name', 'titlePattern', 'descriptionPattern'])
            ->setDefaultSort(['name' => 'ASC'])
            ->setHelp(Crud::PAGE_NEW, self::placeholdersHelp())
            ->setHelp(Crud::PAGE_EDIT, self::placeholdersHelp());
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name');
        yield BooleanField::new('isDefault', 'Default')
            ->setHelp('Used for posts without their own template. Only one template can be the default.');
        yield TextField::new('titlePattern', 'Title')
            ->setHelp('E.g. <code>'.PostSeoResolver::FALLBACK_TITLE_PATTERN.'</code>');
        yield TextareaField::new('descriptionPattern', 'Description')
            ->setHelp('E.g. <code>'.PostSeoResolver::FALLBACK_DESCRIPTION_PATTERN.'</code>');
        yield DateTimeField::new('updatedAt')->hideOnForm()->hideOnIndex();
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);
        $this->keepSingleDefault($entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);
        $this->keepSingleDefault($entityInstance);
    }

    /**
     * HTML list of placeholders shown on SEO forms.
     */
    public static function placeholdersHelp(): string
    {
        $items = '';
        foreach (PostSeoResolver::PLACEHOLDERS as $placeholder => $description) {
            $items .= \sprintf('<li><code>%s</code> — %s</li>', $placeholder, htmlspecialchars($description));
        }

        return 'Available placeholders:<ul>'.$items.'</ul>';
    }

    private function keepSingleDefault(SeoTemplate $template): void
    {
        if ($template->isDefault()) {
            $this->seoTemplateRepository->clearDefaultExcept($template);
        }
    }
}
