<?php

namespace App\Tests\Service\Seo;

use App\Entity\Post;
use App\Entity\SeoTemplate;
use App\Resolver\PostSeoResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PostSeoResolverTest extends KernelTestCase
{
    private PostSeoResolver $resolver;

    protected function setUp(): void
    {
        self::bootKernel();
        static::getContainer()->get(EntityManagerInterface::class)
            ->getConnection()->executeStatement('TRUNCATE seo_template RESTART IDENTITY CASCADE');
        $this->resolver = static::getContainer()->get(PostSeoResolver::class);
    }

    public function testCollapsesWhitespaceLeftByEmptyPlaceholders(): void
    {
        $template = (new SeoTemplate())
            ->setTitlePattern("{short_title}  {tags}\n— {blog_title}")
            ->setDescriptionPattern('{short_description}');

        $seo = $this->resolver->resolve($this->createPost()->setSeoTemplate($template));

        self::assertSame('Rspack — Threads', $seo->title);
    }

    public function testFallsThroughPatternThatRendersEmpty(): void
    {
        // A post without tags makes "{tags}" empty, so the built-in pattern is used instead.
        $post = $this->createPost()->setSeoTitle('{tags}')->setSeoDescription(' ');

        $seo = $this->resolver->resolve($post);

        self::assertSame('Rspack — Threads', $seo->title);
        self::assertSame('About Rspack', $seo->description);
    }

    public function testFindsUnknownPlaceholders(): void
    {
        self::assertSame(['{titel}'], PostSeoResolver::findUnknownPlaceholders('{titel} {title} {titel} {blog_title}'));
        self::assertSame([], PostSeoResolver::findUnknownPlaceholders('{short_title} — {blog_title}'));
    }

    private function createPost(): Post
    {
        return (new Post())
            ->setTitle('Rspack full title')
            ->setShortTitle('Rspack')
            ->setShortDescription('About Rspack')
            ->setText('Body');
    }
}
