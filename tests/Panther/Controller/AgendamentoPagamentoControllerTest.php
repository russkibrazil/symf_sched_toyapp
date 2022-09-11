<?php

namespace App\Tests\Panther\Controller;

use App\Entity\Agendamento;
use App\Entity\AgendamentoPagamentoRequest;
use App\Repository\AgendamentoPagamentoRequestRepository;
use App\Repository\AgendamentoRepository;
use Symfony\Component\Panther\PantherTestCase;

class AgendamentoPagamentoControllerTest extends PantherTestCase
{
    private string $agendamentoId;
    private AgendamentoPagamentoRequestRepository $agendamentoPagamentoRequestRepository;
    private AgendamentoRepository $agendamentoRepository;

    public function setup(): void
    {
        static::bootKernel();
        $doctrine = static::getContainer()->get('doctrine');
        $this->agendamentoPagamentoRequestRepository = $doctrine->getRepository(AgendamentoPagamentoRequest::class);
        $this->agendamentoRepository = $doctrine->getRepository(Agendamento::class);

        $schedule = $this->agendamentoRepository->findBy([
            'formaPagto' => 'CARTAO',
            'concluido' => true,
            'pagamentoPendente' => true,
            'pagamentoPresencial' => false
        ], ['horario' => 'DESC'])[0];
        $this->agendamentoId = $schedule->getId();
    }

    public function testPaymentRequestJs(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/login?emp=38260851000146');

        $h1 = $crawler->filter('h1')->first()->getText();
        if ($h1 == 'Acessar')
        {
            $client->executeScript('document.getElementById("inputUid").value = "isisbrendadaluz"');
            $client->executeScript('document.getElementById("inputPassword").value = "4GH4idMTPt"');
            $client->executeScript('document.querySelector("button[type=submit]").click()');
        }

        $entityCountBefore = count($this->agendamentoPagamentoRequestRepository->findAll());

        $crawler = $client->request('GET', '/agendamentos');
        $workingRow = $crawler->filter(sprintf("#sid%s", $this->agendamentoId));

        $this->assertEquals(1, $workingRow->count(), $this->agendamentoId);
        $workingRow->filter(".dropdown-toggle")->first()->click();

        $client->executeScript("document.querySelector('div.dropdown-menu.show button.btn-pagar').click()");

        $this->assertSelectorWillBeVisible('div.toast');
        $crawler = $client->refreshCrawler();
        $this->assertSelectorTextNotContains('div.toast', '[Object object]', 'Toast message text not printed');
        $this->assertSelectorTextContains('div.toast', 'Requisição de pagamento enviada', 'Something went wrong with the request');

        $this->assertSelectorWillNotBeVisible('div.toast');

        $regs = (self::getContainer()->get(AgendamentoPagamentoRequestRepository::class))->findAll();
        $this->assertGreaterThan($entityCountBefore+1, count($regs), 'Payment Request not included');
    }

    public function testInvalidPaymentRequest(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/login?emp=38260851000146');

        $h1 = $crawler->filter('h1')->first()->getText();
        if ($h1 == 'Acessar')
        {
            $client->executeScript('document.getElementById("inputUid").value = "isisbrendadaluz"');
            $client->executeScript('document.getElementById("inputPassword").value = "4GH4idMTPt"');
            $client->executeScript('document.querySelector("button[type=submit]").click()');
        }

        $entityCountBefore = count($this->agendamentoPagamentoRequestRepository->findAll());

        $crawler = $client->request('GET', '/agendamentos');
        $workingRow = $crawler->filter(sprintf("#sid%s", $this->agendamentoId));

        $this->assertEquals(1, $workingRow->count(), $this->agendamentoId);
        $workingRow->filter(".dropdown-toggle")->first()->click();

        $client->executeScript("document.querySelector('div.dropdown-menu.show button.btn-pagar').click()");

        $this->assertSelectorWillBeVisible('div.toast');
        $crawler = $client->refreshCrawler();
        $this->assertSelectorWillNotBeVisible('div.toast');

        $workingRow->filter(".dropdown-toggle")->first()->click();
        $client->executeScript("document.querySelector('div.dropdown-menu.show button.btn-pagar').click()");

        $this->assertSelectorTextNotContains('div.toast', '[Object object]', 'Toast message text not printed');

        $this->assertSelectorWillNotBeVisible('div.toast');

        $this->assertEquals($entityCountBefore, count($this->agendamentoPagamentoRequestRepository->findAll()), 'Payment Request included');
    }
}
