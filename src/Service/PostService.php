<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Post;
use App\Exception\SlugGenerationException;
use App\Repository\PostRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\String\Slugger\SluggerInterface;
use Throwable;

class PostService implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    public function __construct(
        private PostRepository $postRepository,
        private SluggerInterface $slugger,
    ) {
    }

    public function getById(int $id): ?Post
    {
        return $this->postRepository->find($id);
    }

    /** @return array<Post> */
    public function getPublished(): array
    {
        return $this->postRepository->findPublished();
    }

    public function getPublishedById(int $id): ?Post
    {
        return $this->postRepository->findPublishedById($id);
    }

    public function getPublishedBySlug(string $slug): ?Post
    {
        return $this->postRepository->findPublishedBySlug($slug);
    }

    public function regenerateEmptySlugs(): void
    {
        try {
            foreach($this->postRepository->findAllIterable() as $post) {
                if (!empty($post->getSlug())) {
                    continue;
                }

                $this->computeSlug($post, false);
            }
        } catch (Throwable $exception) {
            $this->logger->error('Some exception has been throwed while trying to regenerate slugs', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw new SlugGenerationException(
                message: 'Unexpected exception throwed in slug regeneration flow',
                previous: $exception,
            );
        }

        $this->postRepository->flush();
    }

    public function computeSlug(Post $post, bool $withSave = true): void
    {
        if ($post->getTitle()) {
            $slug = strtolower((string) $this->slugger->slug($post->getTitle()));
            $post->setSlug($slug);
        }
    }
}
