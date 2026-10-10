<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @template T
 * 
 * @extends ServiceEntityRepository<T>
 */
abstract class BaseRepository extends ServiceEntityRepository
{
    public function flush(): self
    {
        $this->getEntityManager()->flush();

        return $this;
    }

    /** @param T $object */
    public function persist(object $object): self
    {
        $this->getEntityManager()->persist($object);

        return $this;
    }

    /** @param T $object */
    public function remove(object $object): self
    {
        $this->getEntityManager()->remove($object);

        return $this;    
    }
}
