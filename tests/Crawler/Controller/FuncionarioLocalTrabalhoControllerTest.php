<?php

namespace App\Tests\Crawler\Controller;

use App\Repository\PerfilFuncionarioRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

class FuncionarioLocalTrabalhoControllerTest extends WebTestCase
{
    public function testNew()
    {
        $workerUsername = 'rodrigofranciscoalexandre';
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', sprintf('/funcionario/%s/locais/new', $workerUsername));
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Salvar')->form();
        $formName = $form->getName();
        $client->submit($form, [
            ($formName . '[privilegioPrestador]') => 1,
            ($formName . '[salario]') => 1000,
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('funcionario_show', ['nomeUsuario' => $workerUsername]);
    }

    public function testAlreadyExistingLocalTrabalho()
    {
        $workerUsername = 'melissabeneditaelzamelo';
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', sprintf('/funcionario/%s/locais/new', $workerUsername));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert-danger');
        $this->assertRouteSame('funcionario_show', ['nomeUsuario' => $workerUsername]);
    }

    public function testEdit()
    {
        $workerUsername = 'melissabeneditaelzamelo';
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', sprintf('/funcionario/%s/locais/%s/edit', 'melissabeneditaelzamelo', 38260851000146));
        $this->assertResponseIsSuccessful();
    }

    public function testDelete()
    {
        $this->markTestIncomplete('Faulty submit delete');
        $workerUsername = 'victorpviana';
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', sprintf('/funcionario/%s/locais/%s/edit', $workerUsername, 38260851000146));
        $client->submitForm('Apagar', [], 'DELETE');
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('funcionario_show', ['nomeUsuario' => $workerUsername]);
    }
}
