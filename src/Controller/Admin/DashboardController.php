<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->redirectToRoute('admin_post_index');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('My Blog');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Blog');
        yield MenuItem::linkTo(PostCrudController::class, 'Posts', 'fa fa-file-lines');
        yield MenuItem::linkTo(TagCrudController::class, 'Tags', 'fa fa-tags');
        yield MenuItem::linkTo(SeoTemplateCrudController::class, 'SEO templates', 'fa fa-magnifying-glass');
        yield MenuItem::section('Access');
        yield MenuItem::linkTo(UserCrudController::class, 'Users', 'fa fa-user');
    }
}
