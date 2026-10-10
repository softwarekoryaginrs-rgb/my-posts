<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SeoTemplate;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<SeoTemplate>
 */
class SeoTemplateRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeoTemplate::class);
    }

    public function findDefault(): ?SeoTemplate
    {
        return $this->findOneBy(['isDefault' => true], ['id' => 'ASC']);
    }

    public function clearDefaultExcept(SeoTemplate $template): void
    {
        $qb = $this->createQueryBuilder('t')
            ->update()
            ->set('t.isDefault', 'false')
            ->where('t.isDefault = true');

        if (null !== $template->getId()) {
            $qb->andWhere('t.id <> :id')->setParameter('id', $template->getId());
        }

        $qb->getQuery()->execute();
    }
}
