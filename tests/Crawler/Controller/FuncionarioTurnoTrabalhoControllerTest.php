<?php

namespace App\Tests\Crawler\Controller;

use Symfony\Component\BrowserKit\Cookie;
use App\Repository\PerfilFuncionarioRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FuncionarioTurnoTrabalhoControllerTest extends WebTestCase
{
    /**
     * Undocumented function
     *
     * @dataProvider providerIndex
     * @param string $url
     * @param integer $registrosEsperados
     * @return void
     */
    public function testIndex(string $url, int $registrosEsperados): void
    {
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', $url);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Turnos de trabalho');
        $linhasHorario = $crawler->filter('tbody tr');
        $this->assertEquals($registrosEsperados, $linhasHorario->count());
    }

    public function providerIndex(): array
    {
        return [
            'com_escala' => ['/funcionario/melissabeneditaelzamelo/escala', 5],
            'sem_escala' => ['/funcionario/jorgerenangalvao/escala', 0+1],
        ];
    }

    public function testNew()
    {
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', '/funcionario/jorgerenangalvao/escala/new');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Criar Escala de Trabalho');
        $form = $crawler->selectButton('Salvar')->form();
        $formName = $form->getName();
        $form[($formName . '[diaSemana]')]->select('2');
        $form[($formName . '[horaInicio][hour]')]->select('8');
        $form[($formName . '[horaFim][hour]')]->select('18');
        $client->submit($form);

        $crawler = $client->getCrawler();
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('escala_trabalho_index', ['nomeUsuario' => 'jorgerenangalvao']);
        $this->assertSelectorTextContains('h1', 'Turnos de trabalho');

        $itensCardNovo = $crawler->filter("td");
        $this->assertCount(4, $itensCardNovo);
    }

    public function testEdit()
    {
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);
        $client->followRedirects();

        $turnosTarget = $userRepository->find('melissabeneditaelzamelo')->getFuncionarioTurnoTrabalho();

        $crawler = $client->request('GET', '/funcionario/melissabeneditaelzamelo/escala/38260851000146/6/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Editar Escala de Trabalho');

        $form = $crawler->selectButton('Atualizar')->form();
        $formName = $form->getName();
        $this->assertNotEquals('', $formName);
        $form[($formName . '[diaSemana]')]->select('7');
        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Turnos de trabalho');
        $crawler = $client->getCrawler();
        $btnTabelaHorarios  = $crawler->filter('td a.btn-primary');
        $this->assertEquals($turnosTarget->count(), $btnTabelaHorarios->count());
        $cellSabado = $crawler->filterXPath('//td[contains(text(),\'Sábado\')]');
        $this->assertEquals(1, $cellSabado->count());
        $client->request('GET', '/funcionario/melissabeneditaelzamelo/escala/38260851000146/6/edit');
        $this->assertResponseStatusCodeSame(404, 'Não ocorreu a modficação no horário');
    }
}
