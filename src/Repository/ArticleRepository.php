<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function save(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Article[] Returns an array of Article objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Article
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findAllByNameASC(): array
        {
            return $this->createQueryBuilder('a')
                ->orderBy('a.title', 'ASC')
                ->getQuery()
                ->getResult()
            ;
        }
    public function findAllByIDDesc(): array
        {
            return $this->createQueryBuilder('a')
                ->orderBy('a.id', 'DESC')
                ->getQuery()
                ->getResult()
            ;
        }
    public function findAllOnline(): array
        {
            return $this->createQueryBuilder('a')
                ->andWhere('a.online = :val')
                ->setParameter('val', 1)
                ->orderBy('a.id', 'DESC')
                ->getQuery()
                ->getResult()
            ;
        }
    public function find10LastArticles(): array
        {
            return $this->createQueryBuilder('a')
                ->andWhere('a.online = :val')
                ->setParameter('val', 1)
                ->orderBy('a.id', 'DESC')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult()
            ;
        }

    public function findAllByCategory(Categorie $categeory): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.categorie = :val')
            ->setParameter('val', $categeory)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findAllOnlineByCategory(Categorie $categeory): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.categorie = :cateogrie')
            ->setParameter('cateogrie', $categeory)
            ->andWhere('a.online = :online')
            ->setParameter('online', 1)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findLastOnlineByCategory(Categorie $categeory, ?int $limit = null): array
    {
        $query = $this->createQueryBuilder('a')
            ->andWhere('a.categorie = :cateogrie')
            ->setParameter('cateogrie', $categeory)
            ->andWhere('a.online = :online')
            ->setParameter('online', 1)
            ->orderBy('a.id', 'DESC');
            if ($limit){
                $query
                ->setMaxResults($limit);
            }
        return $query->getQuery()
        ->getResult()
        ;
    }
    public function findSuggestionsByKeyword(string $keyword): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.categorie', 'b')
            ->select('a.id','a.title', 'a.image', 'a.updatedAt', 'b.name')
            ->where('a.title LIKE :keyword OR b.name LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->andWhere('a.online = :val')
            ->setParameter('val', 1)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
        ;
    }
    public function findPaginatedArticles($page, $limit)
    {
        $query = $this->createQueryBuilder('a')
            ->leftJoin('a.categorie', 'b')
            ->select('a.id','a.title', 'a.image', 'a.updatedAt', 'b.name')
            ->andWhere('a.online = :val')
            ->setParameter('val', 1)
            ->setFirstResult(($page - 1) * $limit)
            ->orderBy('a.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery();

        return $query->getResult();
    }
}
