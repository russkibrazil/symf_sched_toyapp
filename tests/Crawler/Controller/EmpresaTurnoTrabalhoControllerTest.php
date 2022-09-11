<?php

namespace App\Tests\Crawler\Controller;

use Symfony\Component\BrowserKit\Cookie;
use App\Repository\AgendamentoRepository;
use App\Repository\PerfilFuncionarioRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EmpresaTurnoTrabalhoControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->followRedirects();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);

        $client->request('GET', '/configuracao/38260851000146/horario');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Lista de horários de trabalho');
    }

    public function testDelete()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->followRedirects();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));

        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        /**
         * @var \App\Repository\AgendamentoRepository $aRepository
         */
        $aRepository = static::getContainer()->get(AgendamentoRepository::class);

        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/configuracao/38260851000146/horario');
        $diaSemana = $crawler->filterXPath('//tbody/tr[1]/td[1]')->innerText();

        // datas cobertas pelo mysql
        switch ($diaSemana) {
            case 'Domingo':
                $nDiaSemana = 0;
                break;
            case 'Segunda':
                $nDiaSemana = 1;
                break;
            case 'Terça':
                $nDiaSemana = 2;
                break;
            case 'Quarta':
                $nDiaSemana = 3;
                break;
            case 'Quinta':
                $nDiaSemana = 4;
                break;
            case 'Sexta':
                $nDiaSemana = 5;
                break;
            case 'Sábado':
                $nDiaSemana = 6;
                break;
            default:
                $nDiaSemana = 0;
                break;
        }

        $hoje = new DateTime();
        if ($hoje->format('w') == $nDiaSemana)
        {
            // FIXME Empty IF
            $this->markTestIncomplete('FIXME Empty IF');
        }
        $agendamentosExistentes = $aRepository->findByDiaSemana($nDiaSemana);
        $flashMessagesExpected = count($agendamentosExistentes) > 0 ? 2 : 1;

        $formDelete = $crawler->selectButton('Apagar')->first()->form();
        $client->submit($formDelete);
        $crawler = $client->getCrawler();

        $this->assertSelectorTextContains('h1', 'Lista de horários de trabalho');
        $alerts = $crawler->filter('div.alert');
        $this->assertEquals($flashMessagesExpected, $alerts->count(), 'Revise condições das flash messages');
    }
}
