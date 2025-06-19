<?php

namespace App\Repository;

use App\Entity\Sortiestock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortiestock>
 */
class SortiestockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortiestock::class);
    }

    /**
     * Trouve les dernières sorties avec leurs articles
     */
    public function findRecentWithArticles(int $limit = 10): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.idart', 'a')
            ->addSelect('a')
            ->orderBy('s.datesortie', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule la quantité totale sortie
     */
    public function getTotalQuantitySortie(): int
    {
        $result = $this->createQueryBuilder('s')
            ->select('SUM(s.quantite) as total')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?: 0;
    }

    /**
     * Trouve les sorties par article
     */
    public function findByArticle(int $articleId): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.idart = :articleId')
            ->setParameter('articleId', $articleId)
            ->orderBy('s.datesortie', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les sorties par technicien
     */
    public function findByTechnicien(string $technicien): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.idart', 'a')
            ->addSelect('a')
            ->where('s.technicien = :technicien')
            ->setParameter('technicien', $technicien)
            ->orderBy('s.datesortie', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
