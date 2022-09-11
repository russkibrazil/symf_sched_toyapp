<?php

namespace App\Repository;

use App\Entity\PerfilCliente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PerfilCliente|null find($id, $lockMode = null, $lockVersion = null)
 * @method PerfilCliente|null findOneBy(array $criteria, array $orderBy = null)
 * @method PerfilCliente[]    findAll()
 * @method PerfilCliente[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PerfilClienteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PerfilCliente::class);
    }

    /**
     * Busca os perfis de cliente pelo nome da pessoa
     *
     * @param string $nome
     * @return PerfilCliente[]
     */
    public function findByNome(string $nome)
    {
        return $this->_em->createQuery(
            'SELECT c
            FROM App\Entity\PerfilCliente c
            JOIN App\Entity\Pessoa c.pessoa p
            WHERE p.nome LIKE :nome
            ORDER BY p.nome ASC'
        )
        ->setParameters([
            'nome' => '%'.addcslashes($nome, '%_').'%'
        ])
        ->getResult();
    }
}
