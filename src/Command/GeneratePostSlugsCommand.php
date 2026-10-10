<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\SlugGenerationException;
use App\Service\PostService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'blog:post:generate-empty-slugs')]
final class GeneratePostSlugsCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly PostService $postService,
    ) {
        parent::__construct();
    }

    public function __invoke(): int
    {
        try {
            $this->postService->regenerateEmptySlugs();
        } catch (SlugGenerationException $exception) {
            $this->logger->error('Error while running generate slugs command', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
