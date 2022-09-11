<?php

namespace App\Tests\Crawler\Controller;

use App\Repository\EmpresaRepository;
use App\Repository\PerfilFuncionarioRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

class EmpresaControllerTest extends WebTestCase
{
    public function testIndex()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $client->followRedirects();
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/empresa');
        $this->assertResponseIsSuccessful();
        $companyList = $crawler->filter('.table-responsive');
        $companies = $companyList->filter('tbody > tr')->count();
        $this->assertEquals(1, $companies, 'Incorrect company count');
    }

    public function testShow(): void
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $empresaRepository = static::getcontainer()->get(EmpresaRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        /** @var \App\Entity\Empresa $empresa */
        $empresa = $empresaRepository->find(38260851000146);
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/empresa/38260851000146');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Detalhes da empresa');
        $linhasEsperadasInfo = 9;
        if (strtolower($empresa->getIntervaloBloqueio()) == 'nunca')
        {
            $linhasEsperadasInfo = 5;
        }
        $linhasEsperadasHorario = $empresa->getHorarioTrabalho()->count();
        if ($linhasEsperadasHorario == 0)
        {
            // se não houver horário, então existirá uma linha na tabela comunicando da ausência de registros
            $linhasEsperadasHorario++;
        }

        $this->assertEquals($linhasEsperadasInfo, $crawler->filterXPath('//body/div[3]/div[2]/div/table/tbody/tr')->count());
        $this->assertEquals($linhasEsperadasHorario, $crawler->filterXPath('//body/div[3]/div[3]/div/table/tbody/tr')->count());
    }

    public function testEdit()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', sprintf('/empresa/%s', 38260851000146));
        $this->assertResponseIsSuccessful();
    }

    public function testBlockPolicies()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', sprintf('/empresa/%s/edit_bloqueio', 38260851000146));
        $this->assertResponseIsSuccessful();
    }

    public function testEdicaoInfoBasica()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);
        $crawler = $client->request('GET', '/empresa/38260851000146/edit_basico');

        $this->assertSelectorTextContains('h1', 'Editar Empresa', 'Página não carregada');

        $buttonCrawlerNode = $crawler->selectButton('Salvar');
        $form = $buttonCrawlerNode->form();
        $form['empresa[nomeEmpresa]'] = 'Iago e Miguel Locações de Automóveis ME';
        $form['empresa[endereco]'] = 'Avenida das Nações Unidas 21313';
        $form['empresa[cidade]'] = 'São Paulo';
        $form['empresa[uf]'] = 'SP';
        $form['empresa[cep]'] = '04795924';
        $client->submit($form);

        $this->assertResponseRedirects();
    }
}
