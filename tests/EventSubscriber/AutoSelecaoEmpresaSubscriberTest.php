<?php

namespace App\Tests\EventSubscriber;

use Symfony\Component\Panther\PantherTestCase;

class AutoSelecaoEmpresaSubscriberTest extends PantherTestCase
{
    private const CLIENTE = 'ROLE_USER';
    private const FUNCIONARIO = 'ROLE_FUNCIONARIO';
    private const ADMINISTRADOR = 'ROLE_ADMIN';
    private const PROPRIETARIO = 'ROLE_PROPRIETARIO';

    /**
     * Função para teste da seleção de empresa sem CNPJ
     *
     * Permite que o usuário escolha a empresa que ele quer que o software utilize para selecionar os registros adequados. Válido somente para os casos de usuário proprietário ou cliente
     *
     * @dataProvider usersProvider
     *
     * @param string $usuario
     * @param string $senha
     * @param string $role
     * @return void
     */
    public function testSelecaoEmpresa(string $loginUrl, string $usuario, string $senha, string $role): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', $loginUrl);

        $h1 = $crawler->filter('h1')->first()->getText();
        if ($h1 == 'Acessar')
        {
            $client->executeScript('document.getElementById("inputUid").value = "' . $usuario . '"');
            $client->executeScript('document.getElementById("inputPassword").value = "'. $senha . '"');
            $client->executeScript('document.querySelector("button[type=submit]").click()');
        }
        else
        {
            $client->request('GET', '/logout');
            $this->assertBrowserNotHasCookie('cnpj');
            $client->request('GET', $loginUrl);
            $client->executeScript('document.getElementById("inputUid").value = "' . $usuario . '"');
            $client->executeScript('document.getElementById("inputPassword").value = "'. $senha . '"');
            $client->executeScript('document.querySelector("button[type=submit]").click()');
        }
        if (strpos($loginUrl, '?') === false && ($role == self::CLIENTE || $role == self::PROPRIETARIO))
        {
            $this->assertBrowserNotHasCookie('cnpj');
            $this->assertSelectorTextContains('h1', 'Selecione a empresa');
            $client->executeScript('document.querySelector("form select").selectedIndex = 0');
            $client->executeScript('document.querySelector("button[type=submit]").click()');
        }
        $this->assertSelectorTextContains('h1', 'bem vindo');
        $this->assertBrowserHasCookie('cnpj');
        $client->executeScript('document.getElementById("profile-mgr").click()');
        $crawler = $client->refreshCrawler();
        $el = $crawler->filterXPath('//body/div[1]/div[1]/div[2]/ul[1]/li[3]/div[1]/div[1]/a[2]');
        if ($role == self::CLIENTE || $role == self::PROPRIETARIO)
        {
            $this->assertStringNotContainsStringIgnoringCase('disabled', $el->attr('class'));
        }
        else
        {
            $this->assertStringContainsStringIgnoringCase('disabled', $el->attr('class'));
        }
    }

    public function usersProvider(): array
    {
        return [
            'funcionario sem query' => ['/login', 'melissabeneditaelzamelo', 'xuRAOq7QO5', self::FUNCIONARIO],
            'admin sem query' => ['/login', 'isisbrendadaluz', '4GH4idMTPt', self::ADMINISTRADOR],
            'cliente sem query' => ['/login', 'rodrigofranciscoviana', '4GH4idMTPt', self::CLIENTE],
            'proprietario sem query' => ['/login', 'giovannicauanunes', 'yThppur2LO', self::PROPRIETARIO],

            'funcionario com query' => ['/login?emp=38260851000146', 'melissabeneditaelzamelo', 'xuRAOq7QO5', self::FUNCIONARIO],
            'admin com query' => ['/login?emp=38260851000146', 'isisbrendadaluz', '4GH4idMTPt', self::ADMINISTRADOR],
            'cliente com query' => ['/login?emp=38260851000146', 'rodrigofranciscoviana', '4GH4idMTPt', self::CLIENTE],
            'proprietario com query' => ['/login?emp=38260851000146', 'giovannicauanunes', 'yThppur2LO', self::PROPRIETARIO],
        ];
    }
}
;