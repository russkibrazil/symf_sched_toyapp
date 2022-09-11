<?php

namespace App\Repository;

use App\Entity\PerfilFuncionario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PerfilFuncionario|null find($id, $lockMode = null, $lockVersion = null)
 * @method PerfilFuncionario|null findOneBy(array $criteria, array $orderBy = null)
 * @method PerfilFuncionario[]    findAll()
 * @method PerfilFuncionario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PerfilFuncionarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PerfilFuncionario::class);
    }

    // /**
    //  * @return PerfilFuncionario[] Returns an array of PerfilFuncionario objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PerfilFuncionario
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
