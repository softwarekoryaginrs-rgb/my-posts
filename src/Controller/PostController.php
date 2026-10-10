<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\PostServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

final class PostController extends AbstractController
{
    public function __construct(
        private readonly PostServiceInterface $postService,
    ) {
    }

    #[Route(path: '/', name: 'app_post_index', methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $this->postService->getPublished(),
        ]);
    }

    #[Route(
        path: '/posts/{slug}',
        name: 'app_post_show', 
        requirements: ['slug' => Requirement::ASCII_SLUG],
        methods: [Request::METHOD_GET]
    )]
    public function show(string $slug): Response 
    {
        $result = $this->postService->getPublishedBySlug($slug);
        if ($result === null) {
            throw $this->createNotFoundException();
        }

        return $this->render('post/show.html.twig', [
            'post' => $result,
        ]);
    }
}
