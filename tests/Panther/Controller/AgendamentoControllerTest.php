<?php

namespace App\Tests\Panther\Controller;

use DateTime;
use App\Repository\AgendamentoRepository;
use Symfony\Component\Panther\PantherTestCase;

class AgendamentoControllerTest extends PantherTestCase
{
    /**
     * Undocumented function
     *
     * @return void
     */
    public function testEstorno(): void
    {
        $client = static::createPantherClient();
        /**
         * @var \App\Repository\AgendamentoRepository $aRepo
         */
        $aRepo = static::getContainer()->get(AgendamentoRepository::class);
        $candidates = $aRepo->findBy([ // FIXME Insuficient filter
            'empresa' => '38260851000146',
            'concluido' => 1,
            'pagamentoPendente' => 1,
            'pagamentoPresencial' => 0,
        ]);
        $this->assertNotNull($candidates);
        $this->assertNotEquals([], $candidates);

        $crawler = $client->request('GET', '/login?emp=38260851000146');

        $h1 = $crawler->filter('h1')->first()->getText();
        if ($h1 == 'Acessar')
        {
            $client->executeScript('document.getElementById("inputUid").value = "isisbrendadaluz"');
            $client->executeScript('document.getElementById("inputPassword").value = "4GH4idMTPt"');
            $client->executeScript('document.querySelector("button[type=submit]").click()');
        }

        $crawler = $client->request('GET', '/agendamentos/pendente');

        // $editarBtn = $crawler->filter();
        $chegarBtn = $crawler->filter('.btn-chegada');
        $atrasarBtn = $crawler->filter('.btn-atraso');
        $cancelarBtn = $crawler->filter('.btn-cancela');
        $concluirBtn = $crawler->filter('.btn-concluir');
        $pagarBtn = $crawler->filter('.btn-pagar');
        $estornoBtn = $crawler->filter('.btn-estorno');
        $reembolsarBtn = $crawler->filter('.btn-reembolsar');
        $this->assertEquals(0, $chegarBtn->filter(':not(.disabled)')->count(), 'Botão chegada ativo');
        $this->assertEquals(0, $atrasarBtn->filter(':not(.disabled)')->count(), 'Botão atraso ativo');
        $this->assertEquals(0, $cancelarBtn->filter(':not(.disabled)')->count(), 'Botão cancelamento ativo');
        $this->assertEquals(0, $concluirBtn->filter(':not(.disabled)')->count(), 'Botão conclusão ativo');
        $this->assertEquals(0, $pagarBtn->filter('.disabled')->count(), 'Botão pagamento inativo');
        $this->assertEquals(0, $estornoBtn->filter('.disabled')->count(), 'Botão estorno inativo');
        $this->assertEquals(0, $reembolsarBtn->filter(':not(.disabled)')->count(), 'Botão reembolso ativo');

        $candidateId = $candidates[0]->getId();
        $this->assertNotNull($candidateId);
        // abrir dropdown ações
        $client->executeScript(sprintf('document.getElementById("sid%s").getElementsByTagName("button")[0].click()', $candidateId));
        // requisitar estorno
        $crawler->filterXPath('//div[@class=\'dropdown-menu dropdown-menu-right show\']//button[contains(text(),\'Estornar\')]')->click();

        $this->assertSelectorWillBeVisible('div.toast');
        // $this->assertSelectorTextContains('div.toast-body', 'O pagamento anterior foi estornado');
        $this->assertSelectorWillNotBeVisible('div.toast');
    }

    public function testReembolso()
    {
        $client = static::createPantherClient();
        /**
         * @var \App\Repository\AgendamentoRepository $aRepo
         */
        $aRepo = static::getContainer()->get(AgendamentoRepository::class);
        $candidates = $aRepo->findBy([
            'concluido' => 1,
            'pagamentoPendente' => 0,
            'pagamentoPresencial' => 0,
        ]);
        $this->assertGreaterThan(0, count($candidates));
        $this->assertNotNull($candidates);
        $crawler = $client->request('GET', '/login?emp=38260851000146');

        $h1 = $crawler->filter('h1')->first()->getText();
        if ($h1 == 'Acessar')
        {
            $client->executeScript('document.getElementById("inputUid").value = "isisbrendadaluz"');
            $client->executeScript('document.getElementById("inputPassword").value = "4GH4idMTPt"');
            $client->executeScript('document.querySelector("button[type=submit]").click()');
        }

        $crawler = $client->request('GET', '/agendamentos/pago');

        // testar elementos do menu
        $rows = $crawler->filter('.agendamento-row');
        // $editarBtn = $crawler->filter();
        $chegarBtn = $rows->filter('.btn-chegada');
        $atrasarBtn = $rows->filter('.btn-atraso');
        $cancelarBtn = $rows->filter('.btn-cancela');
        $concluirBtn = $rows->filter('.btn-concluir');
        $pagarBtn = $rows->filter('.btn-pagar');
        $estornoBtn = $rows->filter('.btn-estorno');
        $reembolsarBtn = $rows->filter('.btn-reembolsar');
        $this->assertEquals(0, $chegarBtn->filter(':not(.disabled)')->count(), 'Botão chegada ativo');
        $this->assertEquals(0, $atrasarBtn->filter(':not(.disabled)')->count(), 'Botão atraso ativo');
        $this->assertEquals(0, $cancelarBtn->filter(':not(.disabled)')->count(), 'Botão cancelamento ativo');
        $this->assertEquals(0, $concluirBtn->filter(':not(.disabled)')->count(), 'Botão conclusão ativo');
        $this->assertEquals(0, $pagarBtn->filter(':not(.disabled)')->count(), 'Botão pagamento ativo');
        $this->assertEquals(0, $estornoBtn->filter(':not(.disabled)')->count(), 'Botão estorno ativo');
        $this->assertEquals(0, $reembolsarBtn->filter('.disabled')->count(), 'Botão reembolso inativo');

        $candidateId = $candidates[0]->getId();
        // abrir dropdown ações
        $crawler->filterXPath('//div[@id=\'' . $candidateId .'\']/div[4]/div[1]/button')->first()->click();
        // abrir modal reembolso
        $crawler->filterXPath('//div[@id=\'' . $candidateId .'\']/div[4]/div[1]/div[@class=\'dropdown-menu dropdown-menu-right show\']//button[contains(text(),\'Reembolsar\')]')->first()->click();

        $this->assertSelectorWillBeVisible('#reembolso-modal');
        $crawler = $client->refreshCrawler();
        $enviarReembolsoBtn = $crawler->filter('#reembolso-modal #submit-reembolso-btn');
        $valorInput = $crawler->filter('#reembolso-modal #valor-reembolso-input');
        // testar validação vazio
        $enviarReembolsoBtn->click();
        $this->assertSelectorTextContains('#reembolso-modal .invalid-feedback', 'Digite um valor');
        // enviar valor
        $valorInput->sendKeys('100.01');
        $enviarReembolsoBtn->click();
        $this->assertSelectorWillNotBeVisible('#reembolso-modal');
    }

    public function testNovoAgendamentoForm()
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

        $crawler = $client->request('GET', '/agendamentos/new');
        $this->assertSelectorTextSame('h1', 'Criar um agendamento', 'Página não carregada');

        $crawler->filter('#agendamento_pesquisa_cliente')->first()->sendKeys('Emanuel');
        $this->assertSelectorWillExist('.resultadosAxItem');
        $crawler = $client->getCrawler();
        $client->executeScript('document.getElementsByClassName("resultadosAxItem")[0].click()');

        // Seletor de funcionario => Henry Lucas Nogueira
        $client->executeScript('document.getElementById("agendamento_funcionario").selectedIndex = 2');

        // data. Usar atributo _value_ ou _valueAsNumber_
        $dtAgendamento = new DateTime();
        $dtAgendamento->add(new \DateInterval('P7D'));
        $client->executeScript('document.getElementById("agendamento_horario").value = "' . $dtAgendamento->format('Y-m-d\TH:i') . '"');

        for ($i=0; $i < 3; $i++) {
            $client->executeScript('document.getElementsByClassName("addSvcBtn")[0].click()');
        }
        $servicosCriados = $crawler->filter('.btn-danger');
        $this->assertEquals(3, $servicosCriados->count());

        $client->executeScript('document.getElementsByClassName("btn-danger")[2].click()');
        $crawler = $client->getCrawler();
        $servicosCriados = $crawler->filter('.btn-danger');
        $this->assertEquals(2, $servicosCriados->count());

        $client->executeScript(('document.getElementsByClassName("btn-success")[0].click()'));
        $client->refreshCrawler();
        $this->assertSelectorTextSame('h1', 'Agendamentos futuros', 'Houve problemas ao salvar o registro');
    }

    public function testAgendamentoInterativo()
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

        $crawler = $client->request('GET', '/agendamentos/novoi');
        $this->assertSelectorIsVisible('#cli');

        // Seção cliente
        $crawler->filter('#buscaCliente')->first()->sendKeys('Emanuel');
        $this->assertSelectorWillExist('.resultadosAxItem');
        $crawler = $client->getCrawler();
        $client->executeScript('document.getElementsByClassName("resultadosAxItem")[0].click()');
        $client->executeScript('document.getElementById("nCli").click()');
        $this->assertSelectorWillNotBeVisible('#cli');

        // Seção serviços
        $this->assertSelectorWillBeVisible('#svc');
        $crawler = $client->getCrawler();
        $servicos = $crawler->filter('div#svc div.card');
        $this->assertGreaterThan(0, $servicos->count(), 'Serviços não carregados');
        $client->executeScript('document.querySelectorAll("input[type=checkbox]")[0].checked = true');
        $client->executeScript('document.getElementById("nSvc").click()');
        $this->assertSelectorWillNotBeVisible('#svc');

        //Seção prestador
        $this->assertSelectorWillBeVisible('#func');
        $crawler = $client->getCrawler();
        $funcionarios = $crawler->filter('div#func div.card');
        $this->assertGreaterThan(0, $funcionarios->count(), 'Funcionários não carregados');
        $client->executeScript('document.querySelectorAll("input[type=radio]")[0].checked = true');
        $client->executeScript('document.getElementById("nFunc").click()');
        $this->assertSelectorWillNotBeVisible('#func');

        //Seção horário
        $this->assertSelectorWillBeVisible('#hora');
        $dtAgendamento = new DateTime();
        $dtAgendamento->add(new \DateInterval('P7D'));
        $crawler = $client->getCrawler();
        $crawler->filter('#date')->first()->sendKeys($dtAgendamento->format('dmY')); //Se testado no Brasil
        $client->wait(2,1000);
        $horariosCount = $crawler->filter('option:not(disabled)')->count();
        $this->assertGreaterThan(0, $horariosCount);
        $selectedTimeOption = rand(0, $horariosCount-1);
        $client->executeScript('document.querySelectorAll("option:not(disabled)")[' . $selectedTimeOption . '].selected = true');
        $client->executeScript('document.getElementById("nHora").click()');
        $this->assertSelectorWillNotBeVisible('#hora');

        //Seção resumo
        $this->assertSelectorWillBeVisible('#conclusao');
        $crawler = $client->getCrawler();

        $timePickerValue =  $crawler->filter('#timePicker')->attr('value');
        $conclusaoDtPicker = $crawler->filter('#agendamento_horario')->attr('value');
        $this->assertEquals($dtAgendamento->format('Y-m-d') . 'T' . $timePickerValue, $conclusaoDtPicker);
        $client->executeScript('document.getElementsByClassName("btn-success")[0].click()');
        $crawler = $client->getCrawler();
        $this->assertSelectorTextContains('h1', 'Próximos agendamentos', 'Houve problemas ao salvar o registro');
    }

    public function testEdit()
    {
        $client = static::createPantherClient();
        /**
         * @var \App\Repository\AgendamentoRepository $aRepo
         */
        $aRepo = static::getContainer()->get(AgendamentoRepository::class);
        $candidates = $aRepo->findBy([
            'compareceu' => 0,
            'cancelado' => null,
        ], ['horario' => 'DESC'], 10);

        $crawler = $client->request('GET', '/login?emp=38260851000146');

        $h1 = $crawler->filter('h1')->first()->getText();
        if ($h1 == 'Acessar')
        {
            $client->executeScript('document.getElementById("inputUid").value = "isisbrendadaluz"');
            $client->executeScript('document.getElementById("inputPassword").value = "4GH4idMTPt"');
            $client->executeScript('document.querySelector("button[type=submit]").click()');
        }
        $seekedElement = $candidates[0];
        $crawler = $client->request('GET', '/agendamentos');
        $crawler->filterXPath('//a[contains(text(), \'' . $seekedElement->getHorario(true)->format('d/m/y H:i') . '\')]')->first()->click();
        $crawler = $client->refreshCrawler();

        $this->assertSelectorTextContains('h1', 'Detalhes do agendamento');
        $crawler->filterXPath('//a[contains(text(),\'Editar\')]')->first()->click();
        $crawler = $client->refreshCrawler();

        $this->assertSelectorTextContains('h1', 'Editar agendamento');
        $this->assertSelectorAttributeContains('#agendamento_pesquisa_cliente', 'disabled', 'true');
        $client->executeScript('document.querySelector("button[type=submit]").click()');
        $this->assertSelectorTextContains('h1', 'Agendamentos futuros');
    }

    public function testJs()
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

        $crawler = $client->request('GET', '/agendamentos');

        // teste de cancelamento
        $agendamentosAntes = $crawler->filterXPath('/html/body/div[3]/div/div[4]')->count();
        $client->executeScript('document.querySelectorAll(\'.btn-cancela:not(.disabled)\')[0].click()');
        $this->assertSelectorWillExist('div.toast');
        $crawler = $client->getCrawler();
        $client->executeScript('document.querySelectorAll(\'div#toast-zone a.btn-sm\')[0].click()');
        $client->wait(6);
        $crawler = $client->getCrawler();
        $this->assertEquals($agendamentosAntes, $crawler->filterXPath('/html/body/div[3]/div/div[4]')->count(), 'O registro foi removido');
        $this->assertSelectorWillNotBeVisible('div.toast');

        //teste de atraso

        $client->executeScript('document.querySelectorAll(".btn-atraso:not(.disabled)")[0].click()');
        $this->assertSelectorWillBeVisible('div.toast');
        $this->assertSelectorWillNotBeVisible('div.toast');
        $crawler = $client->getCrawler();
        $btnA = $crawler->filter('.btn-atraso.disabled');
        $this->assertGreaterThanOrEqual(1, $btnA->count());

        // teste de chegada
        $client->executeScript('document.querySelectorAll(".btn-chegada:not(.disabled)")[0].click()');
        $this->assertSelectorWillBeVisible('div.toast');
        $this->assertSelectorWillNotBeVisible('div.toast');
        $crawler = $client->getCrawler();
        $btnC = $crawler->filter('.btn-chegada.disabled');
        $this->assertGreaterThanOrEqual(1, $btnC->count());

        //teste conclusão
        $client->executeScript('document.querySelectorAll(".btn-concluir:not(.disabled)")[0].click()');
        $crawler = $client->getCrawler();
        $btnC = $crawler->filter('.btn-concluir.disabled');
        $this->assertGreaterThanOrEqual(1, $btnC->count());

        $crawler = $client->refreshCrawler();
        $btnPagamento = $crawler->filter(".btn-pagar:not(.disabled)")->first();
        $client->executeScript('document.querySelectorAll(".btn-pagar:not(.disabled)")[0].click()');

        if ($btnPagamento->getAttribute('data-toggle') == 'modal')
        {
            //teste pagamento via Modal
            $this->assertSelectorWillBeVisible('#modalPagamento');
            $client->executeScript('document.getElementById("modalFormNumberValor").value = 300');
            $client->executeScript('document.getElementById("modalFormButtonPagar").click()');
            $crawler = $client->request('GET', '/agendamentos');
            $this->assertLessThan($agendamentosAntes, $crawler->filterXPath('/html/body/div[3]/div/div[4]')->count(), 'O pagamento não foi realizado');
        }
        else
        {
            $this->assertSelectorWillBeVisible('div.toast');
            $this->assertSelectorWillNotBeVisible('div.toast');
        }
    }

}
