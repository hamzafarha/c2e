<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Trouve tous les articles avec leurs mouvements de stock
     */
    public function findAllWithStock(): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.entreestocks', 'e')
            ->leftJoin('a.sortiestocks', 's')
            ->addSelect('e', 's')
            ->orderBy('a.refart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les articles avec un stock critique (en dessous du seuil d'alerte)
     */
    public function findArticlesStockCritique(): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.entreestocks', 'e')
            ->leftJoin('a.sortiestocks', 's')
            ->addSelect('e', 's')
            ->orderBy('a.refart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche d'articles par référence ou nom
     */
    public function findBySearch(string $search): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.refart LIKE :search OR a.nomart LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('a.refart', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
