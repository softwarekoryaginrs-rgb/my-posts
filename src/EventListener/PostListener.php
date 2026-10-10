<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Post;
use App\Service\PostService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Post::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Post::class)]
final readonly class PostListener
{
    public function __construct(
        private PostService $postService,
    ) {
    }

    public function preUpdate(Post $post, LifecycleEventArgs $event): void
    {
        $this->postService->computeSlug($post);
    }

    public function prePersist(Post $blogPost, LifecycleEventArgs $event): void
    {
        $this->postService->computeSlug($blogPost);
    }
}
