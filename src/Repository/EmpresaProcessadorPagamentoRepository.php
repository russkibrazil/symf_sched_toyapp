<?php

namespace App\Repository;

use App\Entity\EmpresaProcessadorPagamento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EmpresaProcessadorPagamento|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmpresaProcessadorPagamento|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmpresaProcessadorPagamento[]    findAll()
 * @method EmpresaProcessadorPagamento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpresaProcessadorPagamentoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmpresaProcessadorPagamento::class);
    }

    // /**
    //  * @return EmpresaProcessadorPagamento[] Returns an array of EmpresaProcessadorPagamento objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EmpresaProcessadorPagamento
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
