<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Event\PostViewEvent;
use App\Repository\PostRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class PostViewListener
{
    public function __construct(
        private PostRepository $postRepository,
    ) {
    }

    #[AsEventListener]
    public function onView(PostViewEvent $event): void
    {
        $this->postRepository->incrementViewsCount($event->postSlug);
    }
}
