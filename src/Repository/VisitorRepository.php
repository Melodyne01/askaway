<?php

namespace App\Repository;

use App\Entity\Visitor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Visitor>
 *
 * @method Visitor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Visitor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Visitor[]    findAll()
 * @method Visitor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VisitorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visitor::class);
    }

    public function save(Visitor $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Visitor $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Visitor[] Returns an array of Visitor objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Visitor
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

public function findAllByDESC(): array
        {
            return $this->createQueryBuilder('v')
                ->orderBy('v.id', 'DESC')
                ->getQuery()
                ->getResult()
            ;
        }

        public function findVisitsByDate(string $year, string $month, string $day, string $hour): array
        {
            $query = $this->createQueryBuilder('v');
            if($year){
                $query = $query->andWhere('YEAR(v.visitedAt) = :year')
                ->setParameter('year', $year);

            }if($month){
            $query = $query->andWhere('MONTH(v.visitedAt) = :month')
                ->setParameter('month', $month);
            }
            if($day){
            $query = $query->andWhere('DAY(v.visitedAt) = :day')
                ->setParameter('day', $day);
            }

            if($hour){
            $query = $query->andWhere('HOUR(v.visitedAt) = :hour')
                ->setParameter('hour', $hour);
            }
        
            $query = $query->orderBy('v.id', 'DESC')
            ->getQuery();
                return $query->getResult()
            ;
        }

        public function findVisitsByPage(): array
        {
            return $this->createQueryBuilder('v')
                ->select('v.page as page')
                ->orderBy('v.page', 'DESC')
                ->getQuery()
                ->getResult()
            ;
        }
}
