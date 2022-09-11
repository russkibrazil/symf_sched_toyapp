<?php
    namespace App\Repository;
    
    use App\Entity\ClienteAvaliacao;
    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
    use Doctrine\Persistence\ManagerRegistry;

    /**
     * @method ClienteAvaliacao|null find($id, $lockMode = null, $lockVersion = null)
     * @method ClienteAvaliacao|null findOneBy(array $criteria, array $orderBy = null)
     * @method ClienteAvaliacao[]    findAll()
     * @method ClienteAvaliacao[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
     */
    class ClienteAvaliacaoRepository extends ServiceEntityRepository
    {

        public function __construct
        (
            ManagerRegistry $registry
        )
        {
            parent::__construct($registry, ClienteAvaliacao::class);
        }

    }