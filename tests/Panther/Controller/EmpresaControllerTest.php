<?php

namespace App\Tests\Panther\Controller;

use Symfony\Component\Panther\PantherTestCase;

class EmpresaControllerTest extends PantherTestCase
{
    public function testEdit()
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

        $crawler = $client->request('GET', '/empresa/38260851000146/edit');
        $this->assertSelectorTextContains('h1', 'Editar Empresa', 'Página não carregada');

        $client->executeScript('document.getElementById("empresa_nomeEmpresa").value = ""');
        $client->executeScript('document.getElementById("empresa_endereco").value = ""');
        $client->executeScript('document.getElementById("empresa_cidade").value = ""');
        $client->executeScript('document.getElementById("empresa_cep").value = ""');

        // preenchimento dos inputs de texto
        $crawler->filter('#empresa_nomeEmpresa')->sendKeys('Iago e Miguel Locações de Automóveis ME');
        $crawler->filter('#empresa_endereco')->sendKeys('Avenida das Nações Unidas 21313');
        $crawler->filter('#empresa_cidade')->sendKeys('São Paulo');
        $crawler->filter('#empresa_uf')->sendKeys('SP');
        $crawler->filter('#empresa_cep')->sendKeys('04795924');

        // $client->executeScript('document.getElementById("empresa_intervaloBloqueio").selectedIndex = 0');
        // $this->assertSelectorWillBeVisible('div.politicas');

        // teste dos ranges
        // $ranges = ['qtdeBloqueio', 'qtdeAnalise', 'atrasosTolerados', 'cancelamentosTolerados'];
        // $val = 0;
        // $spanText = '';
        // foreach ($ranges as $el) {
        //     $val = rand(1, 30);
        //     $client->executeScript('document.getElementById("empresa_' . $el . '").value = ' . $val);
        //     $spanText = $crawler->filter('#range_' . $el . '_value')->text();
        //     $this->assertEquals($val, (int) $spanText);
        // }

        // FIXME O evento jquery não é acionado quando o valor é alterado via JS
        // $client->executeScript('document.getElementById("empresa_intervaloBloqueio").selectedIndex = 2');
        // $this->assertSelectorWillNotBeVisible('div.politicas');

        // teste de inclusão e remoção de itens da lista de horários
        $horariosCadastrados = $crawler->filter('#horarioTrabalho button.btn-sm')->count();
        $client->executeScript('document.getElementsByClassName("addSvcBtn")[0].click()');
        $client->executeScript('document.getElementsByClassName("addSvcBtn")[0].click()');
        $this->assertEquals($horariosCadastrados += 2, $crawler->filter('#horarioTrabalho button.btn-sm')->count(), 'Não foi incluído um item de horário');
        $client->executeScript('document.querySelectorAll("#horarioTrabalho button.btn-sm")[' . (--$horariosCadastrados) . '].click()');
        $this->assertEquals($horariosCadastrados, $crawler->filter('#horarioTrabalho button.btn-sm')->count(), 'Um item de horário não foi excluído');

        $idNovoDia= $crawler->filterXPath('//body/div[3]/div[2]/div/form/div[9]/div[6]/div/div[1]/select')->getAttribute('id');
        $idNovaHora = $crawler->filterXPath('//body/div[3]/div[2]/div/form/div[9]/div[6]/div/div[2]/div[1]/select[1]')->getAttribute('id');
        $client->executeScript('document.getElementById("' . $idNovoDia . '").selectedIndex = 7'); // seleciona o feriado
        $client->executeScript('document.getElementById("' . $idNovaHora . '").selectedIndex = 1');

        $client->executeScript('document.querySelector("button[type=submit]").click()');
        $client->getCrawler();
        $this->assertSelectorTextContains('h1', 'Detalhes da empresa');
    }

    public function testPoliticasBloqueio()
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

        $crawler = $client->request('GET', '/empresa/38260851000146/edit_bloqueio');
        $this->assertSelectorTextContains('h1', 'Bloqueio de Usuários', 'Página não carregada');
    }

}
