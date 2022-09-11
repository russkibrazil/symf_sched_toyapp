<?php
namespace App\Repository;

use App\Entity\Agendamento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method Agendamento|null find($id, $lockMode = null, $lockVersion = null)
 * @method Agendamento|null findOneBy(array $criteria, array $orderBy = null)
 * @method Agendamento[]    findAll()
 * @method Agendamento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendamentoRepository extends ServiceEntityRepository
{
    private $manager;
    public const PAGE_SIZE = 50;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agendamento::class);
        $this->manager = $this->getEntityManager();
    }

    /**
     * Encontrar agendamentos que estão abertos após o período demarcado
     *
     * @param array $args Deve conter as keys [datahora, empresa, usuario, roleUsuario]
     * @return Agendamento[]
     */

    public function findByGreaterThanHorario (array $args)
    {
        if(isset($args['usuario']))
        {
            switch ($args['roleUsuario']) {
                case 'ROLE_USER':
                    $query = $this->manager->createQuery
                    (
                        'SELECT a
                            FROM App\Entity\Agendamento a
                            WHERE a.horario >= :hora AND a.empresa = :pj AND a.cliente = :cpf AND a.cancelado IS NULL AND a.pagamentoPendente = 1
                            ORDER BY a.horario ASC'
                    );

                    break;

                case 'ROLE_PRESTADOR':
                    $query = $this->manager->createQuery
                    (
                        'SELECT a
                            FROM App\Entity\Agendamento a
                            WHERE a.horario >= :hora AND a.empresa = :pj AND a.funcionario = :cpf AND a.cancelado IS NULL AND a.pagamentoPendente = 1
                            ORDER BY a.horario ASC'
                    );
                break;

                default:
                    throw new Exception('No valid role selected');
            }
            $query->setParameters([
                'hora' => $args['datahora'],
                'pj' => $args['empresa'],
                'cpf' => $args['usuario']
            ]);
        }
        else
        {
            $query = $this->manager->createQuery
            (
                'SELECT a
                    FROM App\Entity\Agendamento a
                    WHERE a.horario >= :hora AND a.empresa = :pj AND a.cancelado IS NULL AND a.pagamentoPendente = 1
                    ORDER BY a.horario ASC'
            )
                ->setParameters([
                    'hora' => $args['datahora'],
                    'pj' => $args['empresa'],
                ]);
        }
        $query->setMaxResults(self::PAGE_SIZE);
        return $query->getResult();
    }

    public function findByBetweenHorarioDia($data, $cpfFuncionario) : array
    {
        $datai = $data . ' 00:00:00';
        $dataf = $data . ' 23:59:59';
        $query = $this->manager->createQuery(
            'SELECT a
            FROM App\Entity\Agendamento a
            WHERE a.horario BETWEEN :datai AND :dataf
            AND a.funcionario = :cpfF
            ORDER BY a.horario ASC'
        )->setParameters([
            'datai' => $datai,
            'dataf' => $dataf,
            'cpfF' => $cpfFuncionario
        ]);
        return $query->getResult();
    }

    public function findByDiaSemana(int $diaSemana): array
    {
        $query = $this->manager->createQuery(
            "SELECT a
            FROM App\Entity\Agendamento a
            HAVING DATE_FORMAT(a.horario, '%w') = :diaSemana"
        )->setParameters([
            'diaSemana' => $diaSemana,
        ]);

        return $query->getResult();
    }

    public function getExecutados(bool $pago, int $offset = 0, ?\Datetime $date = null, ?string $nomeUsuario = null): Paginator
    {
        $query = $this->createQueryBuilder('a')
            ->andWhere('a.pagamentoPendente = :paymentStatus')
            ->andWhere('a.concluido = 1')
            ->setParameter('paymentStatus', !$pago)
            ->orderBy('a.horario', 'DESC')
            ->setMaxResults(self::PAGE_SIZE)
            ->setFirstResult($offset)
        ;
        if ($date !== null)
        {
            $dateString = $date->format('Y-m-d');
            $query->andWhere("a.horario BETWEEN '$dateString 00:00:00' AND '$dateString 23:59:59'");
        }
        if ($nomeUsuario !== null)
        {
            $query
                ->andWhere("a.cliente = :usuario OR a.funcionario = :usuario")
                ->setParameter('usuario', $nomeUsuario)
            ;
        }

        return new Paginator($query->getQuery());
    }
}
