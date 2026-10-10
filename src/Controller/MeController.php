<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/me', name: 'app_me', methods: [Request::METHOD_GET])]
final class MeController extends AbstractController
{
    public function __construct(

    ) {
    }

    public function __invoke(): Response
    {
        return $this->render('me/index.html.twig');
    }
}
