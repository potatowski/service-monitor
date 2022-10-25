<?php

namespace App\Repository;

use App\Entity\Registry;
use App\Entity\Route;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Registry>
 *
 * @method Registry|null find($id, $lockMode = null, $lockVersion = null)
 * @method Registry|null findOneBy(array $criteria, array $orderBy = null)
 * @method Registry[]    findAll()
 * @method Registry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegistryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Registry::class);
    }

    public function add(Registry $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Registry $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function getStatus(Route $route, \DateTime $now)
    {
        $qb = $this->createQueryBuilder('ry')
            ->innerJoin('ry.route', 'r')
            ->innerJoin('ry.message', 'm')
            ->where('r.id = :route')
            ->setParameter('route', $route->getId())
            ->andWhere('ry.createAt >= :now')
            ->setParameter('now', $now);
 
            $qb->select("
                SUM (CASE WHEN m.identifier = 'sucess' THEN 1 ELSE 0 END) as success,
                SUM (CASE WHEN m.identifier = 'limited' THEN 1 ELSE 0 END) as limited,
                SUM (CASE WHEN m.identifier = 'failed' THEN 1 ELSE 0 END) as failed,
                COUNT(ry.id) as total
            ");

        return $qb->getQuery()->getOneOrNullResult();
    }
//    /**
//     * @return Registry[] Returns an array of Registry objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Registry
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
