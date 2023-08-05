<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Visitor;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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

        public function findAllByDESC(?int $limit = null): array
        {
            $query = $this->createQueryBuilder('v')
            ->leftJoin('v.page','p')
            ->addSelect('p')
            ->orderBy('v.id', 'DESC');
            if($limit){
                $query->setMaxResults($limit);
            };
            return $query->getQuery()
            ->getResult()
            ;
        }

        public function findOccurrencesByVisitorParam($param, ?string $value = null, ?int $limit = null)
        {
            $query =  $this->createQueryBuilder('v')
                ->select('v.'.$param.', COUNT(v.'.$param.') AS number');
                if($value){
                    $query = $query->where('v.'.$param.' = :value')
                        ->setParameter('value', $value);
                }
                $query = $query->groupBy('v.'.$param)
                ->orderBy('number', 'DESC');
                if($limit){
                    $query->setMaxResults($limit);
                };
            return $query->getQuery()
                ->getResult();
        }
        public function findCountriesByArticle(Article $article)
        {
            $query =  $this->createQueryBuilder('v')
                ->select('v.country', 'COUNT(v.country) AS number')
                ->andWhere('v.page = :article')
                ->setParameter('article', $article)
                ->groupBy('v.country')
                ->orderBy('number', 'DESC');
            return $query->getQuery()
                ->getResult();
        }

        public function findOccurrencesByArticleId($id, ?int $limit = null)
        {
            $query = $this->createQueryBuilder('v')
                ->leftJoin('v.page', 'p')
                ->addSelect('p')
                ->select('p.title, COUNT(p.id) AS number')
                ->where('p.id = :id')
                ->setParameter('id', $id)
                ->orderBy('number', 'DESC');
                if($limit){
                    $query->setMaxResults($limit);
                };
            return $query->getQuery()
                ->getResult();
        }

        public function findAllOccurrencesByArticle(?int $limit = null)
        {
            $query = $this->createQueryBuilder('v')
                ->leftJoin('v.page', 'p')
                ->addSelect('p')
                ->leftJoin('p.categorie', 'c')
                ->addSelect('c')
                ->select('p.id, p.title, COUNT(p.id) AS number, p.image, p.updatedAt, c.name')
                ->groupBy('p.id, p.title, p.image, p.updatedAt, c.name')
                ->orderBy('number', 'DESC');
                if($limit){
                    $query->setMaxResults($limit);
                };
            return $query->getQuery()
                ->getResult();
        }
        

        public function findOccurrencesByCategory(?int $limit = null)
        {
            $query = $this->createQueryBuilder('v')
                ->leftJoin('v.page', 'p')
                ->leftJoin('p.categorie', 'c')
                ->addSelect('COUNT(v.page) as name, COUNT(v) AS number')
                ->groupBy('c.name')
                ->orderBy('number', 'DESC');
                if($limit){
                    $query->setMaxResults($limit);
                };
            return $query->getQuery()
                ->getResult();
        }

        public function findAllVisitsbyCategory(string $category, ?int $limit = null)
        {
            $query = $this->createQueryBuilder('v')
                ->innerJoin('v.page', 'p')
                ->innerJoin('p.categorie', 'c')
                ->andWhere('c.name = :categ')
                ->setParameter('categ', $category)
                ->orderBy('v.visitedAt', 'DESC');
                if($limit){
                    $query->setMaxResults($limit);
                };
            return $query->getQuery()
                ->getResult();
        }

        public function findOccurrencesByCategoryAndRatio(?int $limit = null)
        {
            $query = $this->createQueryBuilder('v')
                ->leftJoin('v.page', 'p')
                ->leftJoin('p.categorie', 'c')
                ->select('c.name, COUNT(v) AS number, COUNT(DISTINCT v.page) as articleNumber, COUNT(v) / COUNT(DISTINCT v.page) as ratio ')
                ->groupBy('c.name')
                ->orderBy('ratio', 'DESC');
                if($limit){
                    $query->setMaxResults($limit);
                };

                $results = $query->getQuery()->getResult();

                foreach ($results as &$result) {
                    $result['ratio'] = round($result['ratio'], 2);
                }
                
    
            return $results;
        }
        public function findOccurrencesByArticleInCategoryAndRatio(string $category, ?int $limit = null)
        {
            $query = $this->createQueryBuilder('v')
                ->leftJoin('v.page', 'p')
                ->addSelect('p')
                ->leftJoin('p.categorie', 'c')
                ->addSelect('c')
                ->select('p.id, p.title, COUNT(p.id) AS number, p.image, p.updatedAt, c.name')
                ->where('c.name = :category')
                ->setParameter(':category', $category)
                ->groupBy('p.id, p.title, p.image, p.updatedAt, c.name')
                ->orderBy('number', 'DESC');
                if($limit){
                    $query->setMaxResults($limit);
                };
            return $query->getQuery()
                ->getResult();
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

        public function findVisitsByDateWithParam(string $year, string $month, string $day, string $hour, string $param = null, mixed $value): array
        {
            $query = $this->createQueryBuilder('v');
            if($param){
                $query = $query->andWhere('v.'.$param.' = :param')
                ->setParameter('param', $value);
            }if($year){
                $query = $query->andWhere('YEAR(v.visitedAt) = :year')
                ->setParameter('year', $year);
            }
            if($month){
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

        public function findVisitsByDateByArticleCategory(string $year, string $month, string $day, string $hour, string $category): array
        {
            $query = $this->createQueryBuilder('v')
                ->leftJoin('v.page', 'p')
                ->leftJoin('p.categorie', 'c')
                ->andWhere('c.name = :categ')
                ->setParameter('categ', $category);
            if($year){
                $query = $query->andWhere('YEAR(v.visitedAt) = :year')
                ->setParameter('year', $year);
            }
            if($month){
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

        public function findVisitsByCountry($country)
        {
            return $this->createQueryBuilder('v')
                ->andWhere('v.country = :country')
                ->setParameter('country', $country)
                ->orderBy('v.visitedAt', 'DESC')
                ->getQuery()
                ->getResult()
            ;
        }

        public function findVisitsByPage($page): array
        {
            return $this->createQueryBuilder('v')
                ->leftJoin('v.page', 'p')
                ->addSelect('p')
                ->andWhere('v.page = :page')
                ->setParameter('page', $page)
                ->orderBy('v.visitedAt', 'DESC')
                ->getQuery()
                ->getResult()
            ;
        }
}
