<?php

namespace App\DataFixtures;

use App\Entity\Empresa;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EmpresaFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $e1 = (new Empresa())
            ->setCnpj(38260851000146)
            ->setNomeEmpresa("Lara e Vinicius Informática ME")
            ->setLogo('ph300-5fa7f3f937685878265717.png')
            ->setIntervaloBloqueio('NUNCA')
            ->setQtdeBloqueio(1)
            ->setIntervaloAnalise('MESES')
            ->setQtdeAnalise(1)
            ->setAtrasosTolerados(30)
            ->setCancelamentosTolerados(95)
            ->setEndereco('Rua Freire da Silva, 842')
            ->setCidade('São Paulo')
            ->setUf('SP')
            ->setCep(29108360)
            ->setQtdeLicencas(1)
        ;

        $e2 = (new Empresa())
            ->setCnpj(82217643000156)
            ->setNomeEmpresa("Alessandra e Hadassa Comercio de Bebidas Ltda")
            ->setLogo('asdasdfasdfasfd.png')
            ->setIntervaloBloqueio('NUNCA')
            ->setQtdeBloqueio(0)
            ->setIntervaloAnalise('MESES')
            ->setQtdeAnalise(1)
            ->setAtrasosTolerados(1000)
            ->setCancelamentosTolerados(1000)
            ->setEndereco('Viela Lima')
            ->setCidade('Carapicuiba')
            ->setUf('SP')
            ->setCep(29108360)
            ->setQtdeLicencas(1)
        ;

        $manager->persist($e1);
        $manager->persist($e2);
        $manager->flush();
    }

}
