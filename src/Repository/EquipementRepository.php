<?php

namespace App\Repository;

use App\Entity\Equipement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Equipement>
 */
class EquipementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipement::class);
    }

    //    /**
    //     * @return Equipement[] Returns an array of Equipement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Equipement
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * @return Equipement[]
     */
    public function findEnPanne(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.etat = :etat')
            ->setParameter('etat', 'en_panne')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Equipement[]
     */
    public function findExigeantIntervention(): array
    {
        // On considère qu'un équipement exige une intervention si la prochaine intervention de l'un de ses interventions est aujourd'hui ou dépassée
        $qb = $this->createQueryBuilder('e')
            ->innerJoin('e.interventions', 'i')
            ->andWhere('i.prochainedate <= :today')
            ->setParameter('today', new \DateTimeImmutable('today'));
        return $qb->getQuery()->getResult();
    }
}
