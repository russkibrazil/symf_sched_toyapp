<?php

namespace App\DataFixtures;

use DateTime;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Empresa;
use App\Entity\FuncionarioTurnoTrabalho;
use App\Entity\PerfilFuncionario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class FuncionarioTurnoTrabalhoFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /**
         * @var \App\Repository\EmpresaRepository $eRepo
         */
        $eRepo = $manager->getRepository(Empresa::class);
        /**
         * @var \App\Repository\PerfilFuncionarioRepository $pfRepo
         */
        $pfRepo = $manager->getRepository(PerfilFuncionario::class);

        $e1 = $eRepo->find(38260851000146);
        $e2 = $eRepo->find(82217643000156);

        $f1 = $pfRepo->find('isisbrendadaluz');
        $f2 = $pfRepo->find('melissabeneditaelzamelo');
        $f3 = $pfRepo->find('henrylucasnogueira');
        $f4 = $pfRepo->find('agathaevelynmendes');
        $f5 = $pfRepo->find('jorgerenangalvao');

        $e1ftt1 = (new FuncionarioTurnoTrabalho())
            ->setCnpj($e1)
            ->setCpfFuncionario($f1)
            ->setDiaSemana(3)
            ->setHoraInicio((new DateTime())->setTime(9,0))
            ->setHoraFim((new DateTime())->setTime(20,0))
        ;
        $e1ftt2 = (new FuncionarioTurnoTrabalho())
            ->setCnpj($e1)
            ->setCpfFuncionario($f2)
            ->setDiaSemana(1)
            ->setHoraInicio((new DateTime())->setTime(10,45))
            ->setHoraFim((new DateTime())->setTime(21,43))
        ;
        $e1ftt3 = (new FuncionarioTurnoTrabalho())
            ->setCnpj($e1)
            ->setCpfFuncionario($f2)
            ->setDiaSemana(2)
            ->setHoraInicio((new DateTime())->setTime(8,0))
            ->setHoraFim((new DateTime())->setTime(22,0))
        ;
        $e1ftt9 = (new FuncionarioTurnoTrabalho())
            ->setCnpj($e1)
            ->setCpfFuncionario($f2)
            ->setDiaSemana(3)
            ->setHoraInicio((new DateTime())->setTime(8,0))
            ->setHoraFim((new DateTime())->setTime(22,0))
        ;
        $e1ftt4 = (new FuncionarioTurnoTrabalho())
            ->setCnpj($e1)
            ->setCpfFuncionario($f2)
            ->setDiaSemana(4)
            ->setHoraInicio((new DateTime())->setTime(8,0))
            ->setHoraFim((new DateTime())->setTime(22,0))
        ;
        $e1ftt8 = (new FuncionarioTurnoTrabalho())
            ->setCnpj($e1)
            ->setCpfFuncionario($f2)
            ->setDiaSemana(5)
            ->setHoraInicio((new DateTime())->setTime(8,0))
            ->setHoraFim((new DateTime())->setTime(22,0))
        ;
        $e1ftt8 = (new FuncionarioTurnoTrabalho())
            ->setCnpj($e1)
            ->setCpfFuncionario($f2)
            ->setDiaSemana(6)
            ->setHoraInicio((new DateTime())->setTime(8,0))
            ->setHoraFim((new DateTime())->setTime(22,0))
        ;
        $e1ftt5 = (new FuncionarioTurnoTrabalho())
            ->setCnpj($e1)
            ->setCpfFuncionario($f3)
            ->setDiaSemana(2)
            ->setHoraInicio((new DateTime())->setTime(9,0))
            ->setHoraFim((new DateTime())->setTime(18,0))
        ;
        $e1ftt6 = (new FuncionarioTurnoTrabalho())
            ->setCnpj($e1)
            ->setCpfFuncionario($f4)
            ->setDiaSemana(6)
            ->setHoraInicio((new DateTime())->setTime(9,0))
            ->setHoraFim((new DateTime())->setTime(19,0))
        ;

        $manager->persist($e1ftt1);
        $manager->persist($e1ftt2);
        $manager->persist($e1ftt3);
        $manager->persist($e1ftt4);
        $manager->persist($e1ftt5);
        $manager->persist($e1ftt6);
        $manager->persist($e1ftt8);
        $manager->persist($e1ftt9);

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
