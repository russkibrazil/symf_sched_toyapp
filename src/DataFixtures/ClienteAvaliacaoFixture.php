<?php

namespace App\DataFixtures;

use App\Entity\ClienteAvaliacao;
use App\Entity\Empresa;
use App\Entity\PerfilCliente;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ClienteAvaliacaoFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
                /**
         * @var \App\Repository\EmpresaRepository $eRepo
         */
        $eRepo = $manager->getRepository(Empresa::class);
        /**
         * @var \App\Repository\PerfilFuncionarioRepository $pfRepo
         */
        $pfRepo = $manager->getRepository(PerfilCliente::class);

        $e1 = $eRepo->find(38260851000146);
        $e2 = $eRepo->find(82217643000156);

        $cli1 = $pfRepo->find('laviniacristianemalusilva');
        $cli2 = $pfRepo->find('victorpietroviana');
        $cli3 = $pfRepo->find('emanuelviniciusrenatoalve');
        $cli4 = $pfRepo->find('sophiejenniferteresinha');
        $cli5 = $pfRepo->find('rodrigofranciscoviana');

        $ficha1 = (new ClienteAvaliacao())
            ->setCnpj($e1)
            ->setCpf($cli1)
        ;
        $ficha2 = (new ClienteAvaliacao())
            ->setCnpj($e1)
            ->setCpf($cli2)
        ;
        $ficha3 = (new ClienteAvaliacao())
            ->setCnpj($e1)
            ->setCpf($cli3)
        ;
        $ficha4 = (new ClienteAvaliacao())
            ->setCnpj($e1)
            ->setCpf($cli4)
        ;
        $ficha5 = (new ClienteAvaliacao())
            ->setCnpj($e1)
            ->setCpf($cli5)
        ;

        $manager->persist($ficha1);
        $manager->persist($ficha2);
        $manager->persist($ficha3);
        $manager->persist($ficha4);
        $manager->persist($ficha5);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            PerfilClienteFixture::class,
            EmpresaFixture::class
        ];
    }
}
