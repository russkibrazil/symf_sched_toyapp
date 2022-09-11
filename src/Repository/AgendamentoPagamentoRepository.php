<?php

namespace App\Repository;

use App\Entity\AgendamentoPagamento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AgendamentoPagamento|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgendamentoPagamento|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgendamentoPagamento[]    findAll()
 * @method AgendamentoPagamento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendamentoPagamentoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendamentoPagamento::class);
    }

    public function findIdInLog(string $id)
    {
        return $this->_em->createQuery(
            "SELECT ap FROM App\Entity\AgendamentoPagamento HAVING
            JSON_CONTAINS(ap.log, '$id', '$[0].id') = 1"
        )
            ->getResult()
        ;
    }
}
