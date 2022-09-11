<?php
    namespace App\Repository;
    
    use App\Entity\Funcionario;
    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
    use Doctrine\Persistence\ManagerRegistry;

/**
     * @method Funcionario|null find($id, $lockMode = null, $lockVersion = null)
     * @method Funcionario|null findOneBy(array $criteria, array $orderBy = null)
     * @method Funcionario[]    findAll()
     * @method Funcionario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
     */
    class FuncionarioRepository extends ServiceEntityRepository
    {

        public function __construct
        (
            ManagerRegistry $registry
        )
        {
            parent::__construct($registry, Funcionario::class);
        }

    }