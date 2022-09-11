<?php

namespace App\Repository;

use App\Entity\PerfilCliente;
use App\Entity\PerfilFuncionario;
use App\Entity\Pessoa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Pessoa|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pessoa|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pessoa[]    findAll()
 * @method Pessoa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PessoaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pessoa::class);
    }

    /**
     * Busca as pessoas pelo nome
     *
     * Esta função é pensada para buscar os clientes pelo nome e retorna alguns dados básicos que podem auxiliar o usuário a identificar o cliente (nome e telefone) e a PK (CPF)
     *
     * @param string $nome
     * @return array
     */
    public function findByNome(string $nome)
    {
        return $this->_em->createQuery(
            'SELECT partial p.{nome, telefone, cpf}, c.nomeUsuario
            FROM App\Entity\Pessoa p
            INNER JOIN App\Entity\PerfilCliente c WITH c.pessoa = p.cpf
            WHERE p.nome LIKE :nome
            ORDER BY p.nome ASC'
        )
        ->execute([
            'nome' => '%'.addcslashes($nome, '%_').'%'
        ], AbstractQuery::HYDRATE_SCALAR);
    }

    /**
     * Erase user and related data
     *
     * @param Pessoa|PerfilCliente|PerfilProfissional $perfil
     * @param boolean $purge If perfil is a Pessoa instance, it confirms that the user really wants to delete everything about him/her and not an undesired request
     * @return void
     * @throws Exception
     */
    public function apagarUsuario ($perfil, bool $purge = false)
    {
        $perfilCliente = '';
        $nomeUsuarioFuncionario = [];
        if ($perfil instanceof Pessoa)
        {
            if (!$purge)
            {
                throw new \Exception('Are you sure you want to delete everything? If yes, try again setting \$purge = true');
            }
            //SELEÇÃO DE PERFIS
            $perfilCliente = $perfil->getPerfilCliente() === null ? '' : $perfil->getPerfilCliente()->getNomeUsuario() ;
            $perfisFuncionario = $perfil->getPerfilFuncionarios()->toArray();
            $nomeUsuarioFuncionario = array_map(
                function ($el) { return $el->getNomeUsuario();},
                $perfisFuncionario
            );

            $inArgument = '';
            foreach ($nomeUsuarioFuncionario as $value) {
                $inArgument .= ('\'' . $value . '\'');
            }
            $query_agendamentos = 'SELECT a.id
                FROM App\Entity\Agendamento a
                WHERE a.cliente = :perfilCliente OR a.funcionario IN (' . $inArgument . ')'
            ;

            $query_agendamentos_param = [
                'perfilCliente' => $perfilCliente,
            ];
            $pessoa = $perfil;
        }

        if ($perfil instanceof PerfilCliente)
        {
            $perfilCliente = $perfil->getNomeUsuario();
            $query_agendamentos = 'SELECT a.id
                FROM App\Entity\Agendamento a
                WHERE a.cliente = :perfilCliente
            ';
            $query_agendamentos_param = [
                'perfilCliente' => $perfilCliente,
            ];
            $pessoa = $perfil->getPessoa();
            $purge = $pessoa->getPerfilFuncionarios() === [] ? true : false;
        }

        if ($perfil instanceof PerfilFuncionario)
        {
            $nomeUsuarioFuncionario = [$perfil->getNomeUsuario()];
            $query_agendamentos = 'SELECT a.id
                FROM App\Entity\Agendamento a
                WHERE a.funcionario = :perfisFuncionario
            ';
            $query_agendamentos_param = [
                'perfisFuncionario' => $perfil->getNomeUsuario(),
            ];
            $inArgument = ('\'' . $perfil->getNomeUsuario() . '\'');
            $pessoa = $perfil->getPessoa();
            $purge = $pessoa->getPerfilFuncionarios()->count() == 1 && $pessoa->getPerfilCliente() == null ? true : false;
        }

        // AGENDAMENTOS
        $agendamentosRes = $this->_em
            ->createQuery($query_agendamentos)
            ->setParameters($query_agendamentos_param)
            ->getResult(AbstractQuery::HYDRATE_SCALAR)
        ;

        if (count($agendamentosRes) > 0)
        {
            $agendamentosArr = array_map(function ($el){ return $el['id'];}, $agendamentosRes);
            $agendamentos = implode(', ', $agendamentosArr);

            $this->_em->createQuery(
                "DELETE App\Entity\AgendamentoServicos ase
                WHERE ase.agendamento IN ($agendamentos)")
                ->execute()
            ;

            $this->_em->createQuery(
                'DELETE App\Entity\AgendamentoPagamento ap
                 WHERE ap.agendamento IN (' . $agendamentos .')')
                ->execute()
            ;

            $this->_em->createQuery(
                'DELETE App\Entity\AgendamentoPagamentoRequest apr
                WHERE apr.agendamento in (' . $agendamentos . ')'
            )
                ->execute()
            ;

            $this->_em->createQuery(
                'DELETE App\Entity\Agendamento a
                WHERE a.id IN (' . $agendamentos .')')
                ->execute()
            ;
        }
        // TODO send every RegistroAcceso related to a file or external DB
        //FUNCIONÁRIO
        if ($nomeUsuarioFuncionario !== [])
        {
            $this->_em->createQuery(
                'DELETE App\Entity\FuncionarioTurnoTrabalho ftt
                WHERE ftt.cpfFuncionario IN (' . $inArgument .')
            ')
                ->execute()
            ;
            $this->_em->createQuery(
                'DELETE App\Entity\FuncionarioLocalTrabalho flt
                WHERE flt.cpfFuncionario IN (' . $inArgument .')
            ')
                ->execute()
            ;
            $this->_em->createQuery(
                'DELETE App\Entity\RegistroAcesso ra
                WHERE ra.usuario IN (' . $inArgument .')
            ')
                ->execute()
            ;
            $this->_em->createQuery(
                'DELETE App\Entity\PerfilFuncionario pf
                WHERE pf.nomeUsuario IN (' . $inArgument .')
            ')
                ->execute()
            ;
        }

        //CLIENTE
        if ($perfilCliente != '')
        {
            $this->_em->createQuery(
                'DELETE App\Entity\ClienteAvaliacao ca
                WHERE ca.cpf = :nomeUsuario
            ')
                ->setParameters(['nomeUsuario' => $perfilCliente])
                ->execute()
            ;

            $this->_em->createQuery(
                'DELETE App\Entity\RegistroAcesso ra
                WHERE ra.usuario = :usuario
            ')
                ->setParameters(['usuario' => $perfilCliente])
                ->execute()
            ;

            $this->_em->createQuery(
                'DELETE App\Entity\PerfilCliente pc
                WHERE pc.nomeUsuario = :nomeUsuario'
            )
                ->setParameters(['nomeUsuario' => $perfilCliente])
                ->execute()
            ;

        }
        if ($purge)
        {
            //PESSOA
            $this->_em->createQuery(
                'DELETE App\Entity\Pessoa u
                WHERE u.cpf = :cpf
            ')
                ->setParameters(['cpf' => $pessoa->getCpf()])
                ->execute()
            ;
        }
    }
}
