<?php

namespace App\Repository;

use App\Entity\Arc;
use App\Entity\Quote;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Quote>
 *
 * @method Quote|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quote|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quote[]    findAll()
 * @method Quote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quote::class);
    }

    public function add(Quote $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Quote $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Quote[] Returns an array of Quote objects
     */
    public function findLastQuoteByArc(Arc $arc): array
    {
        return $this->createQueryBuilder('q')
            ->select('MAX(q.position)')
            ->andWhere('q.arc = :arc')
            ->setParameter('arc', $arc)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    public function findOneBySomeField($value): ?Quote
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
