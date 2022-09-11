<?php

namespace App\DataFixtures;

use App\Entity\PerfilFuncionario;
use App\Entity\Pessoa;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PerfilFuncionarioFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /**
         * @var \App\Repository\PessoaRepository $pRepo
         */
        $pRepo = $manager->getRepository(Pessoa::class);

        $p1 = $pRepo->find('08043377561');
        $p2 = $pRepo->find(38645314622);
        $p3 = $pRepo->find(45244905902);
        $p4 = $pRepo->find(80917584805);
        $p5 = $pRepo->find(86942576026);
        $p6 = $pRepo->find(64955280358);
        $p7 = $pRepo->find('46204113372');
        $pP1 = $pRepo->find('35489673885');

        $pf1 = (new PerfilFuncionario())
            ->setPessoa($p1)
            ->setNomeUsuario('isisbrendadaluz')
            ->setEmail("isisbrendadaluz-80@gemail.com")
            ->setPassword('$argon2id$v=19$m=65536,t=4,p=1$MxyJNIB60YadGwII9cb1hA$jJI1Z3TCxepEpqmh2KnZA+D9y16NC28sT+F4e4A96Hc') // 4GH4idMTPt
            ->setRoles(['ROLE_ADMIN'])
            ->setFoto('ph300-5fa807a047cef725592896.png')
        ;

        $pf2 = (new PerfilFuncionario())
            ->setPessoa($p2)
            ->setNomeUsuario('rodrigofranciscoalexandre')
            ->setEmail("rodrigofranciscoalexandreviana__rodrigofranciscoalexandreviana@deca.com.br")
            ->setPassword('$2y$13$09E2IUfC0s7ZUkGX.MnDz.A5S0QjORZcCleWuha2.MkBcVbzgid0C') // 1Rk5FdSydp
            ->setRoles(['ROLE_FUNCIONARIO'])
        ;

        $pf3 = (new PerfilFuncionario())
            ->setPessoa($p3)
            ->setNomeUsuario('melissabeneditaelzamelo')
            ->setEmail("melissabeneditaelzamelo-75@retrosfessa.com.br")
            ->setPassword('$argon2id$v=19$m=65536,t=4,p=1$ItRQe7uOLZXLl4MQZ2uqJA$i0/FSrn0wNQ8gc3ruRsdOKpm0AjVSKlaEZlDmhDMNL8') // xuRAOq7QO5
            ->setRoles(["ROLE_FUNCIONARIO", "ROLE_PRESTADOR", "ROLE_RECEPCAO"])
        ;

        $pf4 = (new PerfilFuncionario())
            ->setPessoa($p4)
            ->setNomeUsuario('jorgerenangalvao')
            ->setEmail("jorgerenangalvao_@mundivox.com.br")
            ->setPassword('$2y$13$1EMhVZ1.TCX.2yE8HD5vFu7h2Yp352VjlprkmUD/eT1ModNBDQDAG') // tZLieeGrJN
            ->setRoles(['ROLE_FUNCIONARIO', 'ROLE_PRESTADOR'])
        ;

        $pf5 = (new PerfilFuncionario())
            ->setPessoa($p5)
            ->setNomeUsuario('agathaevelynmendes')
            ->setEmail("agathaevelynmendes-87@caocarinho.com.br")
            ->setPassword('$2y$13$PQXhW1TuVIMU.8rK2BNmR.5UUIa55nP/LtefzEoE0slqBfRWaB/9e') // 2kaFXPAADk
            ->setRoles(['ROLE_FUNCIONARIO', 'ROLE_PRESTADOR'])
        ;

        $pf6 = (new PerfilFuncionario())
            ->setPessoa($p6)
            ->setNomeUsuario('henrylucasnogueira')
            ->setEmail("henrylucasnogueira__henrylucasnogueira@netsite.com.br")
            ->setPassword('$2y$13$v2Q9HVE8gcl/Li1Tc3DMBuJZHzZ8PEoGIsj53/nlugpM0vLelRbfe') // X52718bJw5
            ->setRoles(['ROLE_FUNCIONARIO', 'ROLE_PRESTADOR'])
        ;

        $pf7 = (new PerfilFuncionario())
        ->setPessoa($p7)
        ->setNomeUsuario('victorpviana')
        ->setEmail("victorpietroviana_@virtualcriativa.com.br")
        ->setPassword('$2y$13$0eGlRt1wigYaqwK3lHpWMO1Z8SXYCLP0pMyNA4eJda9sBHzeNDF1e') // q3B4pkkF
        ->setRoles(['ROLE_FUNCIONARIO', 'ROLE_CAIXA'])
    ;

        $manager->persist($pf1);
        $manager->persist($pf2);
        $manager->persist($pf3);
        $manager->persist($pf4);
        $manager->persist($pf5);
        $manager->persist($pf6);
        $manager->persist($pf7);

        $pp1 = (new PerfilFuncionario())
            ->setPessoa($pP1)
            ->setNomeUsuario('giovannicauanunes')
            ->setEmail('giovannicauanunes@xerocopiadora.com.br')
            ->setPassword('$2y$13$TByOq6ivKJM62s12e5ZrvOjJ8x97tmx5PntgDiKv0LxyshIRaJnDa') // yThppur2LO
            ->setRoles(['ROLE_PROPRIETARIO'])
        ;

        $manager->persist($pp1);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            PessoaFixture::class
        ];
    }
}