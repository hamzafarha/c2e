<?php

namespace App\Repository;

use App\Entity\Entreestock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Entreestock>
 */
class EntreestockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entreestock::class);
    }

    /**
     * Trouve les dernières entrées avec leurs articles
     */
    public function findRecentWithArticles(int $limit = 10): array
    {
        return $this->createQueryBuilder('e')
            ->join('e.idart', 'a')
            ->addSelect('a')
            ->orderBy('e.dateentree', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule la valeur totale du stock
     */
    public function getTotalStockValue(): float
    {
        $result = $this->createQueryBuilder('e')
            ->select('SUM(e.quantite * e.prixu) as total')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?: 0.0;
    }

    /**
     * Trouve les entrées par article
     */
    public function findByArticle(int $articleId): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.idart = :articleId')
            ->setParameter('articleId', $articleId)
            ->orderBy('e.dateentree', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
