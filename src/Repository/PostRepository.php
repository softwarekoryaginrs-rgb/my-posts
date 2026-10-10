<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<Post>
 */
class PostRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /** @return array<Post> */
    public function findPublished(): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('t')
            ->leftJoin('p.tags', 't')
            ->andWhere('p.published = true')
            ->andWhere('p.deletedAt IS NULL')
            ->orderBy('p.createdAt', 'DESC')
            ->addOrderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPublishedById(int $id): ?Post
    {
        return $this->createQueryBuilder('p')
            ->addSelect('t')
            ->leftJoin('p.tags', 't')
            ->andWhere('p.id = :id')
            ->andWhere('p.published = true')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPublishedBySlug(string $slug): ?Post
    {
        return $this->createQueryBuilder('p')
            ->addSelect('t')
            ->leftJoin('p.tags', 't')
            ->andWhere('p.slug = :slug')
            ->andWhere('p.published = true')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('slug', $slug)
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function incrementViewsCount(string $slug): void
    {
        $this->createQueryBuilder('p')
            ->update()
            ->set('p.viewsCount', 'p.viewsCount + 1')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->execute();
    }

    /** @return iterable<Post> */
    public function findAllIterable(): iterable
    {
        return $this->createQueryBuilder('q')->getQuery()->toIterable();
    }
}
