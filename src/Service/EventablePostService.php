<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Post;
use App\Event\PostViewEvent;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventablePostService implements PostServiceInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private PostService $postService,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getPublishedBySlug(string $slug): Post
    {
        $result = $this->postService->getPublishedBySlug($slug);

        try {
            $this->eventDispatcher->dispatch(new PostViewEvent($slug));
        } catch (Exception $exception) {
            $this->logger->error('Dispatching event exception!', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]); 
        }

        return $result;
    }

	public function getById(int $id): Post 
    {
        return $this->postService->getById($id);
    }

	public function getPublished(): array 
    {
       return $this->postService->getPublished();
    }

	public function getPublishedById(int $id): Post 
    {
        return $this->postService->getPublishedById($id);
    }

	public function regenerateEmptySlugs(): void 
    {
        $this->postService->regenerateEmptySlugs();
    }
}
