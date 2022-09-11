<?php

namespace App\Tests\Crawler\Controller;

use \App\Repository\ServicoRepository;
use Symfony\Component\BrowserKit\Cookie;
use App\Repository\PerfilClienteRepository;
use \App\Repository\PerfilFuncionarioRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServicoControllerTest extends WebTestCase
{
    public function userProvider(): array
    {
        return [
            'teste_admin' => [PerfilFuncionarioRepository::class, 'isisbrendadaluz', true],
            'teste_funcionario' => [PerfilFuncionarioRepository::class, 'jorgerenangalvao', false],
            'teste_cliente' => [PerfilClienteRepository::class, 'rodrigofranciscoviana', false],
        ];
    }

    /**
     * Undocumented function
     *
     * @dataProvider userProvider
     * @param string $cpf
     * @param boolean $is_admin
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
        $crawler = $client->request('GET', '/servicos');

        if ($is_admin) {
            $this->assertSelectorTextContains('h1', 'Serviços');
        } else {
            $this->assertSelectorTextContains('h1', 'Lista de Serviços');
        }
    }

    public function testNew()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', '/servicos/new');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextSame('h1', 'Criar um serviço', 'Página não carregada');

        $form = $crawler->selectButton('Salvar')->form();
        $nomeForm = $form->getName();
        $form[($nomeForm . '[servico]')] = 'Serviço automático';
        $form[($nomeForm . '[descricao]')] = 'Serviço criado para teste automatizado';
        $form[($nomeForm . '[valor]')] = '100.00';
        $form[($nomeForm . '[duracao][hour]')] = '1';
        $client->submit($form);

        $this->assertSelectorTextContains('h1', 'Serviços');
    }

    public function testEdit()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $svcRepo = static::getcontainer()->get(ServicoRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $testSvc = $svcRepo->findOneBy(['servico' => 'Serviço Symfony']);
        $this->assertNotEquals(null, $testSvc, 'Serviço não encontrado');
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', '/servicos/' . $testSvc->getId() . '/edit');
        $this->assertSelectorTextContains('h1', 'Editar serviço');

        $form = $crawler->selectButton('Atualizar')->form();
        $nomeForm = $form->getName();
        $form[($nomeForm . '[servico]')] = 'Serviço automático editado';
        $form[($nomeForm . '[descricao]')] = 'Serviço editado para teste automatizado';
        $form[($nomeForm . '[valor]')] = '200.00';
        $form[($nomeForm . '[duracao][hour]')] = 2;
        $form[($nomeForm . '[duracao][minute]')] = 10;
        $client->submit($form);

        $this->assertSelectorTextContains('h1', 'Serviços');
    }
}
