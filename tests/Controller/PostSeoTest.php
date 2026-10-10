<?php

namespace App\Tests\Controller;

use App\Entity\Post;
use App\Entity\SeoTemplate;
use App\Entity\Tag;
use App\Entity\User;
use App\Set\RoleSet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostSeoTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $this->em->getConnection()->executeStatement('TRUNCATE post, tag, seo_template, "user" RESTART IDENTITY CASCADE');
    }

    public function testShowFallsBackToBuiltInPatternWithoutTemplates(): void
    {
        $post = $this->createPost('Rspack');
        $this->em->flush();

        $this->client->request('GET', '/posts/'.$post->getId());

        self::assertResponseIsSuccessful();
        self::assertPageTitleSame('Rspack — Threads');
        self::assertSelectorExists('meta[name="description"][content="About Rspack"]');
    }

    public function testShowUsesDefaultTemplate(): void
    {
        $this->createTemplate('Default', '{title} | {tags} | {blog_title}', '{short_description} ({reading_time} min, {year})', true);
        $tag = (new Tag())->setName('php');
        $this->em->persist($tag);
        $post = $this->createPost('Rspack')->addTag($tag)->setCreatedAt(new \DateTimeImmutable('2025-03-01'));
        $this->em->flush();

        $this->client->request('GET', '/posts/'.$post->getId());

        self::assertPageTitleSame('Rspack full title | php | Threads');
        self::assertSelectorExists('meta[name="description"][content="About Rspack (1 min, 2025)"]');
        self::assertSelectorExists('meta[property="og:title"][content="Rspack full title | php | Threads"]');
        self::assertSelectorExists('meta[property="og:description"][content="About Rspack (1 min, 2025)"]');
    }

    public function testPostTemplateAndOverridesTakePrecedence(): void
    {
        $this->createTemplate('Default', 'default title', 'default description', true);
        $tutorial = $this->createTemplate('Tutorial', 'Tutorial: {short_title}', 'Learn: {short_description}');
        $post = $this->createPost('Rspack')->setSeoTemplate($tutorial);
        $overridden = $this->createPost('Vite')->setSeoTemplate($tutorial)->setSeoTitle('{title} by {blog_author}');
        $this->em->flush();

        $this->client->request('GET', '/posts/'.$post->getId());
        self::assertPageTitleSame('Tutorial: Rspack');
        self::assertSelectorExists('meta[name="description"][content="Learn: About Rspack"]');

        $this->client->request('GET', '/posts/'.$overridden->getId());
        self::assertPageTitleSame('Vite full title by Roman Koryagin');
        // Description isn't overridden, so it still comes from the post's template.
        self::assertSelectorExists('meta[name="description"][content="Learn: About Vite"]');
    }

    public function testAdminCreatingDefaultTemplateReplacesPreviousDefault(): void
    {
        $old = $this->createTemplate('Old', '{title}', '{short_description}', true);
        $this->em->flush();

        $this->client->loginUser($this->createAdmin());
        $crawler = $this->client->request('GET', '/admin/seo-template/new');
        $form = $crawler->filter('form[name="SeoTemplate"]')->form([
            'SeoTemplate[name]' => 'New',
            'SeoTemplate[titlePattern]' => '{short_title} · {blog_title}',
            'SeoTemplate[descriptionPattern]' => '{short_description}',
        ]);
        $form['SeoTemplate[isDefault]']->tick();
        $this->client->submit($form);

        self::assertResponseRedirects();
        $this->em->clear();
        $templates = $this->em->getRepository(SeoTemplate::class)->findBy(['isDefault' => true]);
        self::assertCount(1, $templates);
        self::assertSame('New', $templates[0]->getName());
        self::assertFalse($this->em->find(SeoTemplate::class, $old->getId())->isDefault());
    }

    public function testAdminPostFormHasSeoFields(): void
    {
        $post = $this->createPost('Rspack');
        $this->em->flush();

        $this->client->loginUser($this->createAdmin());
        $this->client->request('GET', '/admin/post/'.$post->getId().'/edit');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('select[name="Post[seoTemplate]"]');
        self::assertSelectorExists('input[name="Post[seoTitle]"]');
        self::assertSelectorExists('textarea[name="Post[seoDescription]"]');
    }

    public function testAdminRejectsUnknownPlaceholder(): void
    {
        $this->client->loginUser($this->createAdmin());
        $crawler = $this->client->request('GET', '/admin/seo-template/new');
        $this->client->submit($crawler->filter('form[name="SeoTemplate"]')->form([
            'SeoTemplate[name]' => 'Broken',
            'SeoTemplate[titlePattern]' => '{titel} — {blog_title}',
            'SeoTemplate[descriptionPattern]' => '{short_description}',
        ]));

        self::assertResponseStatusCodeSame(422);
        self::assertAnySelectorTextContains('.invalid-feedback', 'Unknown placeholder {titel}');
        self::assertSame(0, $this->em->getRepository(SeoTemplate::class)->count());
    }

    private function createPost(string $shortTitle): Post
    {
        $post = (new Post())
            ->setTitle($shortTitle.' full title')
            ->setShortTitle($shortTitle)
            ->setShortDescription('About '.$shortTitle)
            ->setText('Post body')
            ->setPublished(true);
        $this->em->persist($post);

        return $post;
    }

    private function createTemplate(string $name, string $title, string $description, bool $isDefault = false): SeoTemplate
    {
        $template = (new SeoTemplate())
            ->setName($name)
            ->setTitlePattern($title)
            ->setDescriptionPattern($description)
            ->setIsDefault($isDefault);
        $this->em->persist($template);

        return $template;
    }

    private function createAdmin(): User
    {
        $admin = (new User())
            ->setUsername('admin')
            ->setRoles([RoleSet::ROLE_ADMIN])
            ->setPassword('not-used');
        $this->em->persist($admin);
        $this->em->flush();

        return $admin;
    }
}
