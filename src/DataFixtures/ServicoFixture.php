<?php

namespace App\DataFixtures;

use App\Entity\Empresa;
use App\Entity\Servico;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ServicoFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /**
         * @var \App\Repository\EmpresaRepository $eRepo
         */
        $eRepo = $manager->getRepository(Empresa::class);

        $e1 = $eRepo->find(38260851000146);
        $e2 = $eRepo->find(82217643000156);

        $e1s1 = (new Servico())
            ->setServico("Serviço de teste")
            ->setDescricao("Descrição do serviço de teste 1")
            ->setValor("250.06")
            ->setEmpresa($e1)
            ->setFoto("DhxdezR3E2.jpg")
            ->setDuracao("01:00:00")
        ;

        $e1s2 = (new Servico())
            ->setServico("Serviço de teste")
            ->setDescricao("Descrição do serviço de teste 2")
            ->setValor("484.05")
            ->setEmpresa($e1)
            ->setDuracao("01:05:00")
        ;

        $e1s3 = (new Servico())
            ->setServico("Serviço Symfony")
            ->setDescricao("Meu serviço teste")
            ->setValor("100.00")
            ->setEmpresa($e1)
            ->setFoto("zeXhbb1fFQ.png")
            ->setDuracao("01:00:00")
        ;

        $e1s4 = (new Servico())
            ->setServico("Corte Simples")
            ->setDescricao("O corte simples")
            ->setValor("90.00")
            ->setEmpresa($e1)
            ->setDuracao("00:50:00")
            ->setAtivo(false)
        ;

        $manager->persist($e1s1);
        $manager->persist($e1s2);
        $manager->persist($e1s3);
        $manager->persist($e1s4);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            EmpresaFixture::class
        ];
    }
}
