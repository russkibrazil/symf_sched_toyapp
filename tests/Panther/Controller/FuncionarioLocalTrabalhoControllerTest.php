<?php

namespace App\Tests\Panther\Controller;

use Symfony\Component\Panther\PantherTestCase;

class FuncionarioLocalTrabalhoControllerTest extends PantherTestCase
{
    public function testNew()
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

        $crawler = $client->request('GET', '/funcionario/rodrigofranciscoalexandre/locais/new');
        $this->assertSelectorTextContains('h1', 'Criar local de trabalho');

        $client->executeScript('document.getElementById("funcionario_local_trabalho_privilegioPrestador").click()');
        $crawler->filter('#funcionario_local_trabalho_salario')->sendKeys('1110');
        $client->executeScript('document.querySelector("button[type=submit]").click()');
        $crawler = $client->refreshCrawler();
        $this->assertSelectorTextContains('h1', 'Detalhes do funcionário');
        // $lts = $crawler->filter('.card');
        // $this->assertEquals(2, $lts->count());

        // $itensCardNovo = $crawler->filterXPath('//button[contains(text(), \'Alessandra e Hadassa Comercio de Bebidas Ltda\')]/../../following-sibling::*/descendant::li');
        // $permissoes = explode(',', $itensCardNovo->first()->text());
        // $this->assertCount(1, $permissoes);
    }

    public function testAlreadyExistingLocalTrabalho()
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

        $client->request('GET', '/funcionario/isisbrendadaluz/locais/new');
        $this->assertSelectorTextContains('h1', 'Detalhes do funcionário');
        $this->assertSelectorExists('div.alert');
    }

    public function testEdit(): void
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

        $crawler = $client->request('GET', '/funcionario/agathaevelynmendes/locais/38260851000146/edit');
        $this->assertSelectorTextContains('h1', 'Editar Local de trabalho');
        $checks = $crawler->filter('input[checked]');
        $this->assertEquals(2, $checks->count());
        $this->assertTrue((bool) $crawler->filter('#funcionario_local_trabalho_privilegioPrestador')->getAttribute('checked'));
        $client->executeScript('document.getElementById("funcionario_local_trabalho_privilegioCaixa").click()');
        // $crawler->filter('#funcionario_local_trabalho_salario')->sendKeys('22');
        $client->executeScript('document.querySelector("button[type=submit]").click()');

        $this->assertSelectorTextContains('h1', 'Detalhes do funcionário');
        // $client->executeScript('document.querySelectorAll("h2.mb-0 > button")[1].click()');
        // $crawler = $client->refreshCrawler();
        // $itensCardNovo = $crawler->filterXPath("//button[contains(text(), 'Alessandra e Hadassa Comercio de Bebidas Ltda')]/../../following-sibling::*/descendant::li");
        // $this->assertTrue($itensCardNovo->isDisplayed());
        // $permissoes = $itensCardNovo->first()->text();
        // $this->assertStringContainsStringIgnoringCase('PRESTADOR,CAIXA', $permissoes);
    }

    /**
     * Teste de apresentaçaõ especial para o caso do usuário que é PROPRIETÁRIO
     * @dataProvider proprietarioTestRoutesDataProvider
     * @param string $route
     * @return void
     */
    public function testProprietarioOperations(string $route)
    {
        $client = static::createPantherClient();
        $client->request('GET', '/logout');
        $crawler = $client->request('GET', '/login?emp=38260851000146');

        $h1 = $crawler->filter('h1')->first()->getText();
        if ($h1 == 'Acessar')
        {
            $client->executeScript('document.getElementById("inputUid").value = "giovannicauanunes"');
            $client->executeScript('document.getElementById("inputPassword").value = "yThppur2LO"');
            $client->executeScript('document.querySelector("button[type=submit]").click()');
        }

        $crawler = $client->request('GET', $route);
        $disabledChecksAttr = $crawler->filterXPath("//input[@disabled and @type='checkbox']");
        if (strrpos($route, '/new') !== false)
        {
            $this->assertSelectorTextContains('h1', 'Criar local de trabalho');
            $client->executeScript('document.getElementById("funcionario_local_trabalho_privilegioPrestador").click()');
        }
        else {
            $this->assertSelectorTextContains('h1', 'Editar Local de trabalho');
        }
        $this->assertEquals(3, $disabledChecksAttr->count());
        $this->assertEquals(1, $crawler->filterXPath("//input[@disabled and @type='checkbox' and @checked]")->count());
        $client->executeScript('document.querySelector("button[type=submit]").click()');
        $crawler = $client->refreshCrawler();
        $this->assertSelectorTextContains('h1', 'Detalhes do funcionário');
        $this->assertSelectorTextContains('#detail li.list-group-item', 'ADMIN');

        $client->request('GET', '/logout');
    }

    public function proprietarioTestRoutesDataProvider()
    {
        return [
            'new' => ['/funcionario/giovannicauanunes/locais/new'],
            'edit' => ['/funcionario/giovannicauanunes/locais/38260851000146/edit'],
        ];
    }
}
