<?php

namespace App\DataFixtures;

use App\Entity\Empresa;
use DateTime;
use App\Entity\EmpresaTurnoTrabalho;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EmpresaTurnoTrabalhoFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /**
         * @var \App\Repository\EmpresaRepository $eRepo
         */
        $eRepo = $manager->getRepository(Empresa::class);

        $e1 = $eRepo->find(38260851000146);
        $e2 = $eRepo->find(82217643000156);

        $e1tt1 = (new EmpresaTurnoTrabalho())
            ->setEmpresa($e1)
            ->setDiaSemana(2)
            ->setHoraInicio((new DateTime())->setTime(8,0))
            ->setHoraFim((new DateTime())->setTime(17,30))
        ;
        $e1tt2 = (new EmpresaTurnoTrabalho())
            ->setEmpresa($e1)
            ->setDiaSemana(3)
            ->setHoraInicio((new DateTime())->setTime(8,0))
            ->setHoraFim((new DateTime())->setTime(17,0))
        ;
        $e1tt3 = (new EmpresaTurnoTrabalho())
            ->setEmpresa($e1)
            ->setDiaSemana(4)
            ->setHoraInicio((new DateTime())->setTime(8,0))
            ->setHoraFim((new DateTime())->setTime(17,0))
        ;
        $e1tt4 = (new EmpresaTurnoTrabalho())
            ->setEmpresa($e1)
            ->setDiaSemana(5)
            ->setHoraInicio((new DateTime())->setTime(8,0))
            ->setHoraFim((new DateTime())->setTime(17,0))
        ;
        $e1tt5 = (new EmpresaTurnoTrabalho())
            ->setEmpresa($e1)
            ->setDiaSemana(6)
            ->setHoraInicio((new DateTime())->setTime(8,0))
            ->setHoraFim((new DateTime())->setTime(17,0))
        ;

        $manager->persist($e1tt1);
        $manager->persist($e1tt2);
        $manager->persist($e1tt3);
        $manager->persist($e1tt4);
        $manager->persist($e1tt5);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            EmpresaFixture::class
        ];
    }
}
