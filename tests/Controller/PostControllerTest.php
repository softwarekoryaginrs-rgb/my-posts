<?php

namespace App\Tests\Controller;

use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use App\Set\RoleSet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $this->em->getConnection()->executeStatement('TRUNCATE post, tag, seo_template, "user" RESTART IDENTITY CASCADE');
    }

    public function testIndexListsPublishedPostsNewestFirst(): void
    {
        $tag = (new Tag())->setName('php');
        $this->em->persist($tag);
        $this->createPost('Older post', new \DateTimeImmutable('-2 days'))->addTag($tag);
        $this->createPost('Newer post', new \DateTimeImmutable('-1 day'));
        $this->createPost('Deleted post', new \DateTimeImmutable())->setDeletedAt(new \DateTimeImmutable());
        $this->createPost('Draft post', new \DateTimeImmutable())->setPublished(false);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSame(
            ['Newer post', 'Older post'],
            $crawler->filter('.post-card__title')->each(static fn ($node) => $node->text()),
        );
        self::assertSelectorTextContains('.post-card:last-child .tag', 'php');
        self::assertSelectorTextContains('.post-card .post-meta', 'min read');
    }

    public function testIndexShowsEmptyStateWithoutPosts(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.empty');
    }

    public function testShowRendersMarkdownAndCountsView(): void
    {
        $post = $this->createPost('About Rspack', new \DateTimeImmutable());
        $post->setText("## Build\n\n```js\nexport default {};\n```");
        $this->em->flush();

        $crawler = $this->client->request('GET', '/posts/'.$post->getId());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1.post__title', 'About Rspack');
        self::assertSelectorTextSame('.prose h2', 'Build');
        self::assertCount(1, $crawler->filter('.prose pre code.language-js'));
        self::assertPageTitleContains('About Rspack');

        $this->em->clear();
        self::assertSame(1, $this->em->find(Post::class, $post->getId())->getViewsCount());
    }

    public function testShowReturns404ForDeletedPost(): void
    {
        $post = $this->createPost('Deleted post', new \DateTimeImmutable())->setDeletedAt(new \DateTimeImmutable());
        $this->em->flush();

        $this->client->request('GET', '/posts/'.$post->getId());

        self::assertResponseStatusCodeSame(404);
    }

    public function testShowReturns404ForUnpublishedPost(): void
    {
        $post = $this->createPost('Draft post', new \DateTimeImmutable())->setPublished(false);
        $this->em->flush();

        $this->client->request('GET', '/posts/'.$post->getId());

        self::assertResponseStatusCodeSame(404);
    }

    public function testPreviewRequiresAdmin(): void
    {
        $post = $this->createPost('Draft post', new \DateTimeImmutable())->setPublished(false);
        $this->em->flush();

        $this->client->request('GET', '/admin/preview/posts/'.$post->getId());

        self::assertResponseRedirects('/login');
    }

    public function testAdminCanPreviewDraftWithoutCountingView(): void
    {
        $post = $this->createPost('Draft post', new \DateTimeImmutable())->setPublished(false);
        $this->em->flush();

        $this->client->loginUser($this->createAdmin());
        $this->client->request('GET', '/admin/preview/posts/'.$post->getId());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1.post__title', 'Draft post');
        self::assertSelectorTextContains('.preview-banner', 'draft');
        self::assertSelectorExists('meta[name="robots"][content*="noindex"]');

        $this->em->clear();
        self::assertSame(0, $this->em->find(Post::class, $post->getId())->getViewsCount());
    }

    public function testAdminPostListLinksToPreview(): void
    {
        $post = $this->createPost('Draft post', new \DateTimeImmutable())->setPublished(false);
        $this->em->flush();

        $this->client->loginUser($this->createAdmin());
        $crawler = $this->client->request('GET', '/admin/post');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('a[href="/admin/preview/posts/'.$post->getId().'"]'));
    }

    public function testShowReturns404ForUnknownPost(): void
    {
        $this->client->request('GET', '/posts/999999');

        self::assertResponseStatusCodeSame(404);
    }

    private function createPost(string $title, \DateTimeImmutable $createdAt): Post
    {
        $post = (new Post())
            ->setTitle($title)
            ->setShortTitle($title)
            ->setShortDescription('Description: '.$title)
            ->setText('Post body '.$title)
            ->setCreatedAt($createdAt)
            ->setPublished(true);
        $this->em->persist($post);

        return $post;
    }

    private function createAdmin(): User
    {
        $admin = new User()
            ->setUsername('admin')
            ->setRoles([RoleSet::ROLE_ADMIN])
            ->setPassword('not-used');
        $this->em->persist($admin);
        $this->em->flush();

        return $admin;
    }
}
