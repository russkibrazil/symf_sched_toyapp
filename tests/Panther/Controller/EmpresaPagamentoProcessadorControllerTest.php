<?php

namespace App\Tests\Panther\Controller;

use Symfony\Component\Panther\PantherTestCase;

class EmpresaPagamentoProcessadorControllerTest extends PantherTestCase
{
    public function testNew(): void
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

        $crawler = $client->request('GET', '/empresa/38260851000146/pagamento/processador/new');
        $this->assertSelectorTextContains('h1', 'Configurar processador pagamento');
        $this->assertSelectorExists('.alert-warning', 'O aviso do Mercado pago não foi apresentado');
        $this->assertSelectorAttributeContains('.addPolicyBtn', 'disabled', 'true');
        $this->assertNull($crawler->filter('button[type=submit]')->getAttribute('disabled'));
        $client->executeScript('document.querySelector(\'button[type=submit]\').click()');

        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('h1', 'Detalhes da empresa');
    }
    public function testEditMercadoPago(): void
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

        $crawler = $client->request('GET', '/empresa/38260851000146/pagamento/processador/MERPAGO/edit');
        $this->assertSelectorTextContains('h1', 'Configurar processador pagamento');
        $this->assertSelectorExists('.alert-warning', 'O aviso do Mercado pago não foi apresentado');
        $this->assertSelectorAttributeContains('.addPolicyBtn', 'disabled', 'true');
        $this->assertSelectorAttributeContains('button[type=submit]', 'disabled', 'true');
    }


}
