<?php

declare(strict_types=1);

namespace App\Resolver;

use App\DTO\PostSeo;
use App\Entity\Post;
use App\Repository\SeoTemplateRepository;
use App\Twig\ReadingTimeExtension;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PostSeoResolver
{
    public const array PLACEHOLDERS = [
        '{title}' => 'Post title',
        '{short_title}' => 'Short title',
        '{short_description}' => 'Short description',
        '{tags}' => 'Tag names, comma separated',
        '{reading_time}' => 'Reading time in minutes',
        '{year}' => 'Year the post was created',
        '{blog_title}' => 'Blog title',
        '{blog_author}' => 'Blog author',
    ];

    public const string FALLBACK_TITLE_PATTERN = '{short_title} — {blog_title}';
    public const string FALLBACK_DESCRIPTION_PATTERN = '{short_description}';

    /**
     * @param array{title: string, author: string} $blog
     */
    public function __construct(
        private readonly SeoTemplateRepository $seoTemplateRepository,
        private readonly ReadingTimeExtension $readingTime,
        #[Autowire(param: 'app.blog')]
        private readonly array $blog,
    ) {
    }

    public function resolve(Post $post): PostSeo
    {
        $template = $post->getSeoTemplate() ?? $this->seoTemplateRepository->findDefault();
        $values = $this->placeholderValues($post);

        return new PostSeo(
            title: $this->render([$post->getSeoTitle(), $template?->getTitlePattern(), self::FALLBACK_TITLE_PATTERN], $values),
            description: $this->render([$post->getSeoDescription(), $template?->getDescriptionPattern(), self::FALLBACK_DESCRIPTION_PATTERN], $values),
        );
    }

    /**
     * @return array<string>
     */
    public static function findUnknownPlaceholders(string $pattern): array
    {
        preg_match_all('/\{[a-z_]+\}/', $pattern, $matches);

        return array_values(array_unique(array_diff($matches[0], array_keys(self::PLACEHOLDERS))));
    }

    /**
     * @param array<?string> $patterns
     * @param array<string, string> $values
     */
    private function render(array $patterns, array $values): string
    {
        foreach ($patterns as $pattern) {
            $result = trim(preg_replace('/\s+/u', ' ', strtr((string) $pattern, $values)) ?? '');
            if ('' !== $result) {
                return $result;
            }
        }

        return '';
    }

    /**
     * @return array<string, string>
     */
    private function placeholderValues(Post $post): array
    {
        return [
            '{title}' => (string) $post->getTitle(),
            '{short_title}' => (string) $post->getShortTitle(),
            '{short_description}' => (string) $post->getShortDescription(),
            '{tags}' => implode(', ', $post->getTags()->map(static fn ($tag): string => (string) $tag->getName())->toArray()),
            '{reading_time}' => (string) $this->readingTime->readingTime($post->getText()),
            '{year}' => $post->getCreatedAt()->format('Y'),
            '{blog_title}' => $this->blog['title'],
            '{blog_author}' => $this->blog['author'],
        ];
    }
}
