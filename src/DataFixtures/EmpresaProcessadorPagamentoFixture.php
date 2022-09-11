<?php

namespace App\DataFixtures;

use App\Entity\Empresa;
use App\Entity\EmpresaProcessadorPagamento;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EmpresaProcessadorPagamentoFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $empresa = $manager->getRepository(Empresa::class)->find(38260851000146);
        $epp = (new EmpresaProcessadorPagamento())
            ->setEmpresa($empresa)
            ->setMaxParcelasCartao(12)
            ->setProcessador('MERPAGO')
        ;
        $manager->persist($epp);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            EmpresaFixture::class
        ];
    }
}
