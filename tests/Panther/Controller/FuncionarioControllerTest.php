<?php

namespace App\Tests\Panther\Controller;

use Symfony\Component\Panther\PantherTestCase;

class FuncionarioControllerTest extends PantherTestCase
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

        $crawler = $client->request('GET', '/funcionario');
        $registros = $crawler->filter('.funcionarioRegistros')->count();

        $crawler = $client->request('GET', '/funcionario/new');
        $this->assertSelectorTextContains('h1', 'Novo Funcionário');

        $crawler->filter('#perfil_pessoa_cpf')->first()->sendKeys('17519849236');
        $crawler->filter('#perfil_pessoa_nome')->click();
        $crawler = $client->refreshCrawler();
        $cpfValue = $crawler->filter('#perfil_pessoa_cpf')->attr('value');
        $this->assertEquals('175.198.492-36', $cpfValue, 'Problemas com InputMask');

        $crawler->filter('#perfil_pessoa_nome')->first()->sendKeys('Mariane Alícia Mariah Castro');
        $client->refreshCrawler();
        $this->assertSelectorWillBeEnabled("#perfil_pessoa_nome");
        $this->assertSelectorWillBeEnabled("#perfil_pessoa_telefone");
        $this->assertSelectorWillBeEnabled("#perfil_pessoa_endereco");
    }

    /**
     * Testando Validador de nome de usuário
     * @dataProvider invalidUsernameProvider
     * @param string $username
     * @return void
     */
    public function testNewInvalidUsername(string $username): void
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
        $crawler = $client->request('GET', '/funcionario/new');

        $crawler->filter('#perfil_pessoa_cpf')->first()->sendKeys('67561767579');
        $crawler->filter('#perfil_pessoa_nome')->first()->sendKeys('Ana Catarina Rezende');
        $crawler->filter('#perfil_pessoa_telefone')->first()->sendKeys('69993776253');
        $crawler->filter('#perfil_pessoa_endereco')->first()->sendKeys('Rua Líbero Badaró, 333');
        $crawler->filter('#perfil_email')->first()->sendKeys('anacatarinarezende-91@hotmail.it');
        $crawler->filter('#perfil_nomeUsuario')->first()->sendKeys($username);

        $client->executeScript('document.querySelector("button[type=submit]").click()');

        $this->assertSelectorExists('div.invalid-feedback', 'Há campos com problemas na validação');
    }

    public function invalidUsernameProvider(): array
    {
        return [
            'perfil cliente' => ['victorpietroviana'],
            'perfil funcionário' => ['jorgerenangalvao'],
        ];
    }

    public function testNewWithAlreadyExistentPerson()
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
        $crawler = $client->request('GET', '/funcionario/new');
        $crawler->filter('#perfil_pessoa_cpf')->first()->sendKeys('38645314622');

        $crawler->filter('#perfil_pessoa_nome')->first()->sendKeys('Benedita Nair Marcela da Silva');
        $client->refreshCrawler();
        $this->assertSelectorWillBeDisabled("#perfil_pessoa_nome");
        $this->assertSelectorWillBeDisabled("#perfil_pessoa_telefone");
        $this->assertSelectorWillBeDisabled("#perfil_pessoa_endereco");
    }

    /**
     * Undocumented function
     * @return void
     */
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
        $client->request('GET', '/funcionario/rodrigofranciscoalexandre/edit');
        $this->assertSelectorTextContains('h1', 'Editar Funcionário');
        $client->executeScript('document.getElementById("perfil_pessoa_nome").value = ""');
        $client->executeScript('document.getElementById("perfil_email").value = ""');
        $client->executeScript('document.getElementById("perfil_pessoa_telefone").value = ""');
        $client->executeScript('document.getElementById("perfil_pessoa_endereco").value = ""');

        $crawler = $client->refreshCrawler();
        $crawler->filter('#perfil_pessoa_nome')->first()->sendKeys('Marina Rebeca Milena de Paula');
        $crawler->filter('#perfil_email')->first()->sendKeys('marinarebecamilenadepaula@transporteveloz.com.br');
        $crawler->filter('#perfil_pessoa_telefone')->first()->sendKeys('85981722476');
        $crawler->filter('#perfil_pessoa_endereco')->first()->sendKeys('Vila Manuel Passos, 885');

        $client->executeScript('document.querySelector("button[type=submit]").click()');
        $crawler = $client->refreshCrawler();
        $this->assertSelectorTextContains('h1', 'Lista de funcionários');
        $novoRegistro = $crawler->filterXPath('//a[contains(text(),\'Marina Rebeca Milena de Paula\')]');
        $this->assertEquals(1, $novoRegistro->count());
    }
}
