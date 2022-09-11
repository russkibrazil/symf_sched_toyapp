<?php
    namespace App\Repository;

    use App\Entity\FuncionarioTurnoTrabalho;
    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
    use Doctrine\Persistence\ManagerRegistry;

    /**
     * @method FuncionarioTurnoTrabalho|null find($id, $lockMode = null, $lockVersion = null)
     * @method FuncionarioTurnoTrabalho|null findOneBy(array $criteria, array $orderBy = null)
     * @method FuncionarioTurnoTrabalho[]    findAll()
     * @method FuncionarioTurnoTrabalho[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
     */
    class FuncionarioTurnoTrabalhoRepository extends ServiceEntityRepository
    {

        public function __construct
        (
            ManagerRegistry $registry
        )
        {
            parent::__construct($registry, FuncionarioTurnoTrabalho::class);
        }

    }