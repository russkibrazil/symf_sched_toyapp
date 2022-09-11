<?php

namespace App\Tests\Crawler\Controller;

use App\Repository\PerfilClienteRepository;
use App\Repository\PerfilFuncionarioRepository;
use App\Repository\PerfilRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

class FuncionarioControllerTest extends WebTestCase
{
/**
     * @dataProvider userProvider
     *
     * @param integer $cpf
     * @param bool $is_admin
     * @return void
     */
    public function testIndex(string $fqrcn, string $nomeUsuario, bool $is_admin): void
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get($fqrcn);
        $testUser = $userRepository->find($nomeUsuario);
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', '/funcionario');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Lista de funcionários');
        // vamos verificar se o botão de novo é visivel só para admin
        $botaoCriar = 0;
        if ($is_admin){
            $botaoCriar++;
        }
        $this->assertEquals($botaoCriar, $crawler->filter('div.col > a.btn-primary')->count());
    }

    public function userProvider(): array
    {
        return [
            'teste_admin' => [PerfilFuncionarioRepository::class, 'isisbrendadaluz', true],
            'teste_funcionario' => [PerfilFuncionarioRepository::class, 'jorgerenangalvao', false],
            'teste_cliente' => [PerfilClienteRepository::class, 'rodrigofranciscoviana', false]
        ];
    }

    /**
     * Undocumented function
     * @dataProvider userProvider
     * @return void
     */
    public function testShow(string $fqrcn, string $nomeUsuario, bool $is_allowed)
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get($fqrcn);
        $testUser = $userRepository->find($nomeUsuario);
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', '/funcionario/jorgerenangalvao');

        $expectedH1 = $is_allowed ? 'Detalhes do funcionário' : 'bem vindo' ;
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $expectedH1);
    }

    public function testNew()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', "/funcionario/new");
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Salvar')->form();

        $formName = $form->getName();
        $client->submit($form, [
            ($formName . '[pessoa][cpf]') => '20669270407',
            ($formName . '[pessoa][nome]') => 'André Juan Teixeira',
            ($formName . '[pessoa][telefone]') => '84981075956',
            ($formName . '[pessoa][endereco]') => 'Rua Líbero Badaró, 333',
            ($formName . '[email]') => 'andre.juan.teixeira@cbb.com.br',
            ($formName . '[nomeUsuario]') => 'andre.juan',
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('funcionario_index', [], 'Errors found while saving');
    }

    public function testNewInvalid()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', "/funcionario/new");
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Salvar')->form();

        $formName = $form->getName();
        $client->submit($form, [
            ($formName . '[pessoa][cpf]') => '20669270407',
            ($formName . '[pessoa][nome]') => 'André Juan Teixeira',
            ($formName . '[pessoa][telefone]') => '84981075956',
            ($formName . '[pessoa][endereco]') => 'Rua Líbero Badaró, 333',
            ($formName . '[email]') => 'andre.juan.teixeira@cbb.com.br',
            ($formName . '[nomeUsuario]') => 'isisbrendadaluz',
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('funcionario_new', [], 'The record has been saved');
    }

    public function testNewAlreadyExistingPessoa()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', "/funcionario/new");
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Salvar')->form();

        $formName = $form->getName();
        $client->submit($form, [
            ($formName . '[pessoa][cpf]') => '60859755703',
            ($formName . '[pessoa][nome]') => 'Emanuel Vinicius Renato Alves',
            ($formName . '[pessoa][telefone]') => '86993132070',
            ($formName . '[pessoa][endereco]') => 'Rua Líbero Badaró, 333',
            ($formName . '[email]') => 'andre.juan.teixeira@cbb.com.br',
            ($formName . '[nomeUsuario]') => 'emvira',
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('funcionario_index', [], 'Errors found while saving');
    }

    public function testEdit()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', sprintf('/funcionario/%s/edit', 'agathaevelynmendes'));
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Salvar')->form();

        $formName = $form->getName();
        $client->submit($form, [
            ($formName . '[pessoa][telefone]') => '84981075957',
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('funcionario_index', [], 'Errors found while saving');
    }
}
