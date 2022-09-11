<?php

namespace App\DataFixtures;

use App\Entity\Pessoa;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PessoaFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $u1 = (new Pessoa())
            ->setCpf('08043377561')
            ->setNome("Igor")
            ->setTelefone('32542353252')
        ;

        $u2 = (new Pessoa())
            ->setCpf(38645314622)
            ->setNome("Cauã Emanuel Nascimento")
            ->setTelefone('11000000000')
        ;

        $u3 = (new Pessoa())
            ->setCpf(41459746546)
            ->setNome("Lavínia Cristiane Malu Silva")
            ->setTelefone('91999461139')
        ;

        $u4 = (new Pessoa())
            ->setCpf(45244905902)
            ->setNome("Andreia Letícia Melo")
            ->setTelefone('12123456789')
        ;

        $u6 = (new Pessoa())
            ->setCpf(46204113372)
            ->setNome("Victor Pietro Viana")
            ->setTelefone('69981532695')
        ;

        $u7 = (new Pessoa())
            ->setCpf(60859755703)
            ->setNome("Emanuel Vinicius Renato Alves")
            ->setTelefone('86993132070')
        ;

        $u8 = (new Pessoa())
            ->setCpf(64955280358)
            ->setNome("Henry Lucas Nogueira")
            ->setTelefone('95988512619')
            ->setEndereco("Rua Nilo Brandão")
        ;

        $u9 = (new Pessoa())
            ->setCpf(80917584805)
            ->setNome("Raimundo César Cauê Moura")
            ->setTelefone('11111110111')
        ;

        $u10 = (new Pessoa())
            ->setCpf(86942576026)
            ->setNome("Agatha Evelyn Mendes")
            ->setTelefone('96993035883')
        ;

        $u11 = (new Pessoa())
            ->setCpf(89038252099)
            ->setNome("João da Silva")
            ->setTelefone('22222222222')
        ;
        $u12 = (new Pessoa())
            ->setCpf('35489673885')
            ->setNome('Giovanni Cauã Nunes')
            ->setTelefone('21987772151')
        ;

        $manager->persist($u1);
        $manager->persist($u2);
        $manager->persist($u3);
        $manager->persist($u4);
        $manager->persist($u6);
        $manager->persist($u7);
        $manager->persist($u8);
        $manager->persist($u9);
        $manager->persist($u10);
        $manager->persist($u11);
        $manager->persist($u12);

        $manager->flush();
    }
}
