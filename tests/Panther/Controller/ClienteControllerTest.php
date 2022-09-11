<?php

namespace App\Tests\Panther\Controller;

use Symfony\Component\Panther\PantherTestCase;

class ClienteControllerTest extends PantherTestCase
{
    public function testValidNew(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/logout');
        $crawler = $client->request('GET', '/login?emp=38260851000146');

        $h1 = $crawler->filter('h1')->first()->getText();
        if ($h1 == 'Acessar')
        {
            $client->executeScript('document.getElementById("inputUid").value = "isisbrendadaluz"');
            $client->executeScript('document.getElementById("inputPassword").value = "4GH4idMTPt"');
            $client->executeScript('document.querySelector("button[type=submit]").click()');
        }

        $crawler = $client->request('GET', '/cliente/new');
        $this->assertSelectorTextContains('h1', 'Novo Cliente');

        $crawler->filter('#perfil_pessoa_cpf')->first()->sendKeys('29416985821');
        $crawler->filter('#perfil_pessoa_nome')->click();
        $crawler = $client->refreshCrawler();
        $cpfValue = $crawler->filter('#perfil_pessoa_cpf')->attr('value');
        $this->assertEquals('294.169.858-21', $cpfValue, 'Problemas com InputMask');
        $crawler->filter('#perfil_pessoa_nome')->first()->sendKeys('Benedita Nair Marcela da Silva');
        $client->refreshCrawler();
        $this->assertSelectorWillBeEnabled("#perfil_pessoa_nome");
        $this->assertSelectorWillBeEnabled("#perfil_pessoa_telefone");
        $this->assertSelectorWillBeEnabled("#perfil_pessoa_endereco");
    }

    public function testNewWithAlreadyExistentPerson(): void
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

        $crawler = $client->request('GET', '/cliente/new');
        $this->assertSelectorTextContains('h1', 'Novo Cliente');
        $crawler->filter('#perfil_pessoa_cpf')->first()->sendKeys('38645314622');

        $crawler->filter('#perfil_pessoa_nome')->first()->sendKeys('Benedita Nair Marcela da Silva');
        $client->refreshCrawler();
        $this->assertSelectorWillBeDisabled("#perfil_pessoa_nome");
        $this->assertSelectorWillBeDisabled("#perfil_pessoa_telefone");
        $this->assertSelectorWillBeDisabled("#perfil_pessoa_endereco");
    }
}