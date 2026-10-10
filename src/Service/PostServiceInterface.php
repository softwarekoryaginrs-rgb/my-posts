<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Post;

interface PostServiceInterface
{
    public function getPublishedById(int $id): Post;

    public function getById(int $id): ?Post;

    /** @return array<Post> */
    public function getPublished(): array;

    public function getPublishedBySlug(string $slug): ?Post;

    public function regenerateEmptySlugs(): void;
}
