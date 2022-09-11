<?php

namespace App\Tests\Controller;

use Symfony\Component\Panther\PantherTestCase;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;

class RegistrationControllerTest extends PantherTestCase
{
    use MailerAssertionsTrait;

    public function testRegistrarUsuario(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/');

        $h1 = $crawler->filter('h2');
        if ($h1->count() > 0)
        {
            $client->request('GET', '/logout');
        }

        $crawler = $client->request('GET', '/register');
        $this->assertSelectorTextContains('h1', 'Registro');
        $crawler->filter('#registration_form_pessoa_cpf')->first()->sendKeys('35899071508');
        $crawler->filter('#registration_form_pessoa_nome')->first()->sendKeys('Tereza Fátima dos Santos');
        $crawler->filter('#registration_form_pessoa_telefone')->first()->sendKeys('61992221697');
        $crawler->filter('#registration_form_pessoa_endereco')->first()->sendKeys('Rua dos bobos, 0');

        $crawler->filter('#registration_form_email')->first()->sendKeys('terezafatimadossantos-85@aol.com');
        $crawler->filter('#registration_form_nomeUsuario')->first()->sendKeys('terezafatimadossantos');
        $crawler->filter('#registration_form_plainPassword')->first()->sendKeys('HQR5NOA5Xk');
        $client->executeScript('document.getElementById("registration_form_agreeTerms").click()');

        $client->executeScript('document.querySelector("button[type=submit]").click()');
        $this->assertSelectorNotExists('div.invalid-feedback'); // FIXME Create a specific route for testing this

        $crawler = $client->refreshCrawler();
        $this->assertSelectorNotExists('div.alert-danger');
        $this->assertSelectorTextContains('h1', 'Seja bem vindo!');
        // $this->assertEmailCount(1);
        // $this->assertBrowserCookieValueSame('cnpj', 38260851000146);
    }
}
