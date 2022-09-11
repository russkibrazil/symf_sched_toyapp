<?php

namespace App\Repository;

use App\Entity\AgendamentoPagamentoRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AgendamentoPagamentoRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgendamentoPagamentoRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgendamentoPagamentoRequest[]    findAll()
 * @method AgendamentoPagamentoRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendamentoPagamentoRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendamentoPagamentoRequest::class);
    }

    // /**
    //  * @return AgendamentoPagamentoRequest[] Returns an array of AgendamentoPagamentoRequest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AgendamentoPagamentoRequest
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
