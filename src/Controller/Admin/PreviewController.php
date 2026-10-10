<?php

declare(strict_types=1);

namespace App\Controller\Admin;
    
use App\Service\PostService;
use App\Set\RoleSet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PreviewController extends AbstractController
{
    public function __construct(
        private readonly PostService $postService,
    ) {
    }

    #[Route(
        path: '/admin/preview/posts/{id}',
        name: 'app_post_preview',
        requirements: ['id' => Requirement::POSITIVE_INT],
        methods: [Request::METHOD_GET]
    )]
    #[IsGranted(RoleSet::ROLE_ADMIN)]
    public function preview(int $id): Response
    {
        $result = $this->postService->getById($id);
        if ($result === null) {
            throw $this->createNotFoundException("Post with id $id doesnt exists!");
        }

        return $this->render('post/show.html.twig', [
            'post' => $result,
            'preview' => true,
        ]);
    }
}
