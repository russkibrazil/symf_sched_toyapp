<?php

namespace App\DataFixtures;

use App\Entity\Agendamento;
use App\Entity\AgendamentoServicos;
use App\Entity\Empresa;
use App\Entity\PerfilCliente;
use App\Entity\PerfilFuncionario;
use App\Entity\Servico;
use DateInterval;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AgendamentoFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $pcRepo = $manager->getRepository(PerfilCliente::class);
        $pfRepo = $manager->getRepository(PerfilFuncionario::class);
        $cli  = $pcRepo->find('rodrigofranciscoviana');
        $cli2 = $pcRepo->find('laviniacristianemalusilva');
        $fun  = $pfRepo->find('jorgerenangalvao');
        $fun2 = $pfRepo->find('melissabeneditaelzamelo');

        $emp = $manager->getRepository(Empresa::class)->find(38260851000146);
        $s = $manager->getRepository(Servico::class)->findOneBy(['servico' => 'Serviço Symfony']);

        $ag1 = (new Agendamento())
            ->setCliente($cli)
            ->setFuncionario($fun)
            ->setHorario('2021-12-22 11:30:00')
            ->setEmpresa($emp)
            ->setFormaPagto("Dinheiro")
            ->setConclusaoEsperada('2021-12-22 12:35:00')
            ->setPagamentoPresencial(true)
        ;

        $svc1 = (new AgendamentoServicos())
            ->setAgendamento($ag1)
            ->setServico($s)
        ;

        $ag2 = (new Agendamento())
            ->setCliente($cli)
            ->setFuncionario($fun)
            ->setHorario(date_add(new DateTime(), new DateInterval('P1M'))->format('Y-m-d H:i:00'))
            ->setEmpresa($emp)
            ->setFormaPagto("Dinheiro")
            ->setConclusaoEsperada(date_add(new DateTime(), new DateInterval('P1MT1H5M'))->format('Y-m-d H:i:00'))
            ->setPagamentoPresencial(false)
            ->setAtrasado(true)
        ;

        $svc2 = (new AgendamentoServicos())
            ->setAgendamento($ag2)
            ->setServico($s)
        ;

        $ag3 = (new Agendamento())
            ->setCliente($cli)
            ->setFuncionario($fun)
            ->setHorario(date_add(new DateTime(), new DateInterval('PT2H'))->format('Y-m-d H:i:00'))
            ->setEmpresa($emp)
            ->setFormaPagto("Dinheiro")
            ->setConclusaoEsperada(date_add(new DateTime(), new DateInterval('PT3H5M'))->format('Y-m-d H:i:00'))
            ->setPagamentoPresencial(true)
            ->setCompareceu(true)
        ;

        $svc3 = (new AgendamentoServicos())
            ->setAgendamento($ag3)
            ->setServico($s)
        ;

        $ag4 = (new Agendamento())
            ->setCliente($cli)
            ->setFuncionario($fun)
            ->setHorario(date_sub(new DateTime(), new DateInterval('PT5H5M'))->format('Y-m-d H:i:00'))
            ->setEmpresa($emp)
            ->setFormaPagto("Dinheiro")
            ->setConclusaoEsperada(date_sub(new DateTime(), new DateInterval('PT4H'))->format('Y-m-d H:i:00'))
            ->setPagamentoPresencial(true)
            ->setCompareceu(true)
            ->setConcluido(true)
        ;

        $svc4 = (new AgendamentoServicos())
            ->setAgendamento($ag4)
            ->setServico($s)
        ;

        $ag5 = (new Agendamento())
            ->setCliente($cli)
            ->setFuncionario($fun)
            ->setHorario(date_sub(new DateTime(), new DateInterval('PT7H5M'))->format('Y-m-d H:i:00'))
            ->setEmpresa($emp)
            ->setFormaPagto("Dinheiro")
            ->setConclusaoEsperada(date_sub(new DateTime(), new DateInterval('PT6H'))->format('Y-m-d H:i:00'))
            ->setPagamentoPresencial(true)
            ->setCompareceu(true)
            ->setConcluido(true)
            ->setPagamentoPendente(false)
        ;

        $svc5 = (new AgendamentoServicos())
            ->setAgendamento($ag5)
            ->setServico($s)
        ;

        $ag6 = (new Agendamento())
            ->setCliente($cli2)
            ->setFuncionario($fun)
            ->setHorario(date_sub(new DateTime(), new DateInterval('PT9H5M'))->format('Y-m-d H:i:00'))
            ->setEmpresa($emp)
            ->setFormaPagto("Cartao")
            ->setConclusaoEsperada(date_sub(new DateTime(), new DateInterval('PT8H'))->format('Y-m-d H:i:00'))
            ->setPagamentoPresencial(false)
            ->setCompareceu(true)
            ->setConcluido(true)
            ->setPagamentoPendente(false)
        ;

        $svc6 = (new AgendamentoServicos())
            ->setAgendamento($ag6)
            ->setServico($s)
        ;

        $ag7 = (new Agendamento())
            ->setCliente($cli2)
            ->setFuncionario($fun2)
            ->setHorario(date_sub(new DateTime(), new DateInterval('PT5H5M'))->format('Y-m-d H:i:00'))
            ->setEmpresa($emp)
            ->setFormaPagto("Dinheiro")
            ->setConclusaoEsperada(date_sub(new DateTime(), new DateInterval('PT4H'))->format('Y-m-d H:i:00'))
            ->setPagamentoPresencial(false)
            ->setCompareceu(true)
            ->setConcluido(true)
        ;

        $svc7 = (new AgendamentoServicos())
            ->setAgendamento($ag7)
            ->setServico($s)
        ;

        $ag8 = (new Agendamento())
            ->setCliente($cli)
            ->setFuncionario($fun)
            ->setHorario(date_add(new DateTime(), new DateInterval('PT2H'))->format('Y-m-d H:i:00'))
            ->setEmpresa($emp)
            ->setFormaPagto("CARTAO")
            ->setConclusaoEsperada(date_add(new DateTime(), new DateInterval('PT3H5M'))->format('Y-m-d H:i:00'))
            ->setPagamentoPresencial(false)
            ->setCompareceu(true)
            ->setConcluido(true)
            ->setPagamentoPendente(true)
        ;

        $svc8 = (new AgendamentoServicos())
            ->setAgendamento($ag8)
            ->setServico($s)
        ;

        $manager->persist($ag1);
        $manager->persist($svc1);
        $manager->persist($ag2);
        $manager->persist($svc2);
        $manager->persist($ag3);
        $manager->persist($svc3);
        $manager->persist($ag4);
        $manager->persist($svc4);
        $manager->persist($ag5);
        $manager->persist($svc5);
        $manager->persist($ag6);
        $manager->persist($svc6);
        $manager->persist($ag7);
        $manager->persist($svc7);
        $manager->persist($ag8);
        $manager->persist($svc8);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            PerfilClienteFixture::class,
            PerfilFuncionarioFixture::class,
            EmpresaFixture::class
        ];
    }
}
