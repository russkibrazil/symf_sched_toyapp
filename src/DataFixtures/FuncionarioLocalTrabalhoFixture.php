<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use App\Entity\Empresa;
use App\Entity\FuncionarioLocalTrabalho;
use App\Entity\PerfilFuncionario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class FuncionarioLocalTrabalhoFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /**
         * @var \App\Repository\EmpresaRepository $eRepo
         * @var \App\Repository\PerfilFuncionarioRepository $pfRepo
         */
        $eRepo = $manager->getRepository(Empresa::class);
        $pfRepo = $manager->getRepository(PerfilFuncionario::class);

        $e1 = $eRepo->find(38260851000146);
        $e2 = $eRepo->find(82217643000156);

        $f1 = $pfRepo->find('isisbrendadaluz');
        $f2 = $pfRepo->find('melissabeneditaelzamelo');
        $f3 = $pfRepo->find('henrylucasnogueira');
        $f4 = $pfRepo->find('agathaevelynmendes');
        $f5 = $pfRepo->find('jorgerenangalvao');
        $f6 = $pfRepo->find('victorpviana');

        $e1flt1 = (new FuncionarioLocalTrabalho())
            ->setCnpj($e1)
            ->setCpfFuncionario($f1)
            ->setAtivo(false)
            ->setPrivilegios(["PRESTADOR", "ADMIN"])
        ;
        $e1flt2 = (new FuncionarioLocalTrabalho())
            ->setCnpj($e1)
            ->setCpfFuncionario($f2)
            ->setPrivilegios(["PRESTADOR", "RECEPCAO"])
        ;
        $e1flt3 = (new FuncionarioLocalTrabalho())
            ->setCnpj($e1)
            ->setCpfFuncionario($f3)
            ->setPrivilegios(["PRESTADOR"])
            ->setSalario(1500)
            ->setComissao(0.01)
        ;
        $e1flt4 = (new FuncionarioLocalTrabalho())
            ->setCnpj($e1)
            ->setCpfFuncionario($f5)
            ->setPrivilegios(["PRESTADOR"])
        ;
        $e1flt5 = (new FuncionarioLocalTrabalho())
            ->setCnpj($e1)
            ->setCpfFuncionario($f4)
            ->setPrivilegios(["PRESTADOR"])
        ;
        $e1flt6 = (new FuncionarioLocalTrabalho())
            ->setCnpj($e1)
            ->setCpfFuncionario($f6)
            ->setPrivilegios(["CAIXA"])
        ;

        $e2flt1 = (new FuncionarioLocalTrabalho())
            ->setCnpj($e2)
            ->setCpfFuncionario($f1)
            ->setPrivilegios(["PRESTADOR", "CAIXA"])
        ;
        $e2flt2 = (new FuncionarioLocalTrabalho())
            ->setCnpj($e2)
            ->setCpfFuncionario($f5)
            ->setPrivilegios(["PRESTADOR"])
            ->setSalario(1000)
            ->setComissao(1)
        ;

        $manager->persist($e1flt1);
        $manager->persist($e1flt2);
        $manager->persist($e1flt3);
        $manager->persist($e1flt4);
        $manager->persist($e1flt5);
        $manager->persist($e1flt6);
        $manager->persist($e2flt1);
        $manager->persist($e2flt2);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            EmpresaFixture::class,
            PerfilFuncionarioFixture::class
        ];
    }
}
