<?php
    namespace App\Repository;

    use App\Entity\EmpresaTurnoTrabalho;
    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
    use Doctrine\ORM\EntityManagerInterface;
    use Doctrine\Persistence\ManagerRegistry;

    /**
     * @method EmpresaTurnoTrabalho|null find($id, $lockMode = null, $lockVersion = null)
     * @method EmpresaTurnoTrabalho|null findOneBy(array $criteria, array $orderBy = null)
     * @method EmpresaTurnoTrabalho[]    findAll()
     * @method EmpresaTurnoTrabalho[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
     */
    class EmpresaTurnoTrabalhoRepository extends ServiceEntityRepository
    {

        public function __construct
        (
            ManagerRegistry $registry
        )
        {
            parent::__construct($registry, EmpresaTurnoTrabalho::class);
        }

    }