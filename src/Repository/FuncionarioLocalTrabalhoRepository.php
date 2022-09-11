<?php
    namespace App\Repository;

    use App\Entity\FuncionarioLocalTrabalho;
    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
    use Doctrine\Persistence\ManagerRegistry;
    use Exception;

    /**
     * @method FuncionarioLocalTrabalho|null find($id, $lockMode = null, $lockVersion = null)
     * @method FuncionarioLocalTrabalho|null findOneBy(array $criteria, array $orderBy = null)
     * @method FuncionarioLocalTrabalho[]    findAll()
     * @method FuncionarioLocalTrabalho[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
     */
    class FuncionarioLocalTrabalhoRepository extends ServiceEntityRepository
    {

        public function __construct
        (
            ManagerRegistry $registry
        )
        {
            parent::__construct($registry, FuncionarioLocalTrabalho::class);
        }

        /**
         * @param array|string $privilegio
         * @param int $cnpj
         * @return FuncionarioLocalTrabalho[]
         */
        public function findFuncionarioByPrivilegio($privilegio, string $cnpj = '')
        {
            $sql = '';
            if (is_string($privilegio))
            {
                $sql = '
                    SELECT flt
                    FROM \App\Entity\FuncionarioLocalTrabalho flt
                    WHERE JSON_SEARCH(flt.privilegios, \'one\', \'' . strtoupper($privilegio) . '\') IS NOT NULL'
                ;
            }
            elseif (is_array($privilegio))
            {
                $sql = '
                    SELECT flt
                    FROM \App\Entity\FuncionarioLocalTrabalho flt
                    WHERE JSON_SEARCH(flt.privilegios, \'one\', \'' . strtoupper($privilegio[0]) . '\') IS NOT NULL'
                ;
                for ($i=1; $i < count($privilegio); $i++) {
                    $sql = $sql . ' OR JSON_SEARCH(flt.privilegios, \'one\', \'' . strtoupper($privilegio[$i]) . '\') IS NOT NULL';
                }
            }
            else
                throw new Exception('Array or string expected');

            if ($cnpj != '')
            {
                $sql = $sql . " AND flt.cnpj = $cnpj";
            }

            $q = $this->_em->createQuery($sql);

            return $q->getResult();
        }

    }