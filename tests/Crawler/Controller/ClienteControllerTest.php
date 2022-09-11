<?php

namespace App\Tests\Crawler\Controller;

use App\Repository\PerfilClienteRepository;
use App\Repository\PerfilFuncionarioRepository;
use App\Repository\PerfilRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

class ClienteControllerTest extends WebTestCase
{
    public function testNew()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', "/cliente/new");
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Salvar')->form();

        $formName = $form->getName();
        $client->submit($form, [
            ($formName . '[pessoa][cpf]') => '67561767579',
            ($formName . '[pessoa][nome]') => 'Ana Catarina Rezende',
            ($formName . '[pessoa][telefone]') => '69993776253',
            ($formName . '[pessoa][endereco]') => 'Rua Líbero Badaró, 333',
            ($formName . '[email]') => 'anacatarinarezende-91@hotmail.it',
            ($formName . '[nomeUsuario]') => 'anacrezende',
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('agendamentos_index', [], 'Errors found while saving');
    }

    public function testInsertAlreadyExistingPessoa()
    {
        $this->markTestIncomplete('WIP');
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', "/cliente/new");
        $this->assertResponseIsSuccessful();
    }

        /**
     * Undocumented function
     * @dataProvider userProvider
     *
     * @param string $fqrcn
     * @param string $nomeUsuario
     * @return void
     */
    public function testEdit(string $fqrcn, string $nomeUsuario): void
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get($fqrcn);
        $testUser = $userRepository->find($nomeUsuario);
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', '/cliente/rodrigofranciscoviana/edit');
        $this->assertResponseIsSuccessful();
    }

    public function testEditAccess()
    {
        $this->markTestIncomplete('WIP');
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
     *
     * @param string $fqrcn
     * @param string $nomeUsuario
     * @return void
     */
    public function testShow(string $fqrcn, string $nomeUsuario): void
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get($fqrcn);
        $testUser = $userRepository->find($nomeUsuario);
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', '/cliente/rodrigofranciscoviana');
        $this->assertResponseIsSuccessful();
    }

        /**
     * Undocumented function
     * @dataProvider apagarUserProvider
     * @param string $nomeUsuario
     * @param string $senha
     * @return void
     */
    public function testDeleteProfile(string $nomeUsuario, string $senha): void
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilClienteRepository::class);
        $testUser = $userRepository->find($nomeUsuario);
        $this->assertNotNull($testUser, 'Confirme o nome de usuário fornecido');
        $hasPerfilProfissional = $testUser->getPessoa()->getPerfilFuncionarios() === [] ? false : true;
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', "/cliente/$nomeUsuario/apagar");
        $this->assertResponseIsSuccessful();

        if ($hasPerfilProfissional)
        {
            $this->assertSelectorTextContains('.alert-danger', 'Contudo, seus perfis profissionais não serão modificados nem apagados');
        }
        else {
            $this->assertSelectorTextNotContains('.alert-danger', 'Contudo, seus perfis profissionais não serão modificados nem apagados');
        }
        $crawler = $client->getCrawler();
        $form = $crawler->filter('button.btn-danger')->form();
        $formName = $form->getName();
        $client->submit($form, [
            ($formName . '[email]') => $testUser->getEmail(),
            ($formName . '[senha][first]') => $senha,
            ($formName . '[senha][second]') => $senha,
            ($formName . '[ciente]') => '1'
        ]);
        $crawler = $client->getCrawler();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a.btn-block', 'Not redirected');
        $this->assertNull($userRepository->find($nomeUsuario));
    }

    public function apagarUserProvider(): array
    {
        return [
            'has_pro_profiles' => ['rodrigofranciscoviana', '4GH4idMTPt'],
            'has_no_pro_profiles' => ['victorpietroviana', 'q3B4pkkF']
        ];
    }
}
