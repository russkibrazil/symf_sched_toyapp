<?php

namespace App\DataFixtures;

use App\Entity\PerfilCliente;
use App\Entity\Pessoa;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PerfilClienteFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /**
         * @var \App\Repository\PessoaRepository $pRepo
         */
        $pRepo = $manager->getRepository(Pessoa::class);

        $p1 = $pRepo->find(41459746546);
        $p2 = $pRepo->find(46204113372);
        $p3 = $pRepo->find(60859755703);
        $p4 = $pRepo->find(89038252099);
        $p5 = $pRepo->find(38645314622);

        $pc1 = (new PerfilCliente())
            ->setPessoa($p1)
            ->setNomeUsuario('laviniacristianemalusilva')
            ->setEmail("laviniacristianemalusilva@bom.com.br")
            ->setPassword('$argon2id$v=19$m=65536,t=4,p=1$o+n693poaPuk4IvokubOQQ$JBCXpbkoFJdCdeZVxYreEeQNqRMGbtYyqIdSJqsfR5w')
        ;

        $pc2 = (new PerfilCliente())
            ->setPessoa($p2)
            ->setNomeUsuario('victorpietroviana')
            ->setEmail("victorpietroviana_@virtualcriativa.com.br")
            ->setPassword('$2y$13$0eGlRt1wigYaqwK3lHpWMO1Z8SXYCLP0pMyNA4eJda9sBHzeNDF1e') // q3B4pkkF
        ;

        $pc3 = (new PerfilCliente())
            ->setPessoa($p3)
            ->setNomeUsuario('emanuelviniciusrenatoalve')
            ->setEmail("emanuelviniciusrenatoalves@integrasjc.com.br")
            ->setPassword('$argon2id$v=19$m=65536,t=4,p=1$5wC6/WDc5im7gupGF4pAqQ$nuVdW/AnyQogmUOg0Uo1yi5IU6Crc4Z107pv3n4tR1Q')
        ;

        $pc4 = (new PerfilCliente())
            ->setPessoa($p4)
            ->setNomeUsuario('sophiejenniferteresinha')
            ->setEmail("sophiejenniferteresinhaoliveira@grupomozue.com.br")
            ->setPassword('$argon2id$v=19$m=65536,t=4,p=1$WEn7b64I9744kRJICEpaLA$jcYLDvh2bZsZPakMDGsncpbfIZwR6lN0QcgJOOSerK0')
        ;

        $pc5 = (new PerfilCliente())
        ->setPessoa($p5)
        ->setNomeUsuario('rodrigofranciscoviana')
        ->setEmail("rodrigofranciscoalexandreviana@deca.com.br")
        ->setPassword('$argon2id$v=19$m=65536,t=4,p=1$MxyJNIB60YadGwII9cb1hA$jJI1Z3TCxepEpqmh2KnZA+D9y16NC28sT+F4e4A96Hc') // 4GH4idMTPt
    ;

        $manager->persist($pc1);
        $manager->persist($pc2);
        $manager->persist($pc3);
        $manager->persist($pc4);
        $manager->persist($pc5);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            PessoaFixture::class
        ];
    }
}