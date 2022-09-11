<?php

namespace App\Tests\Crawler\Controller;

use App\Repository\PerfilFuncionarioRepository;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EmpresaPagamentoProcessadorTest extends WebTestCase
{
    public function testNew()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 82217643000156));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find('giovannicauanunes');
        $client->followRedirects();
        $client->loginUser($testUser);

        $crawler = $client->request('GET', sprintf('/empresa/%s/pagamento/processador/new', 82217643000156));
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Salvar')->form();
        $formName = $form->getName();

        $client->submit($form, [
            ($formName . '[processador]') => 'MERPAGO',
            ($formName . '[maxParcelasCartao]') => 12
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('configuracao_show', ['cnpj' => 82217643000156], 'Found errors while saving');
    }

    public function testDelete()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', '/empresa/38260851000146');
        $processadoresPagamento = $crawler->filter('button[type=submit]');
        $deleteForm = $processadoresPagamento->first()->form();
        $client->submit($deleteForm);
        $crawler = $client->getCrawler();
        $processadoresPagamentoAfter = $crawler->filter('button[type=submit]');
        $this->assertLessThan($processadoresPagamento->count(), $processadoresPagamentoAfter->count());
    }

    public function testEdit()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', sprintf('/empresa/%s/pagamento/processador/%s/edit', 38260851000146, 'MERPAGO'));
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Salvar')->form();
        $processadorSelect = $crawler->filter(sprintf("#%s_processador", $form->getName()));
        $this->assertNotNull($processadorSelect->attr('disabled'), 'Payment processor field is enabled');
    }
}
