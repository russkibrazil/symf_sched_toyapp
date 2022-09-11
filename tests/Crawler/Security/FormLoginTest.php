<?php

namespace App\Tests\Crawler\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FormLoginTest extends WebTestCase
{
    public function testLoginSemCnpj(): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/login');
        $this->assertResponseIsSuccessful('Problema ao renderizar a página de login');
        $this->assertSelectorTextContains('h1', 'Acessar', 'Há um usuário logado.');

        $buttonCrawlerNode = $crawler->selectButton('Acessar');
        $form = $buttonCrawlerNode->form();
        $form['_username'] = 'melissabeneditaelzamelo';
        $form['_password'] = 'xuRAOq7QO5';
        $client->submit($form);

        $client->followRedirects();
        $this->assertBrowserHasCookie('cnpj','/',null, 'O cookie de CNPJ não foi definido para o funcionário.');
        $this->assertRouteSame('home', [], 'Redirecionado para a rota incorreta.');
        $this->assertResponseIsSuccessful('Login não concluído');
    }

    public function testLoginComCnpj()
    {
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/login', ['emp' => 38260851000146]);
        $this->assertResponseIsSuccessful('Problema ao renderizar a página de login');
        $this->assertSelectorTextContains('h1', 'Acessar', 'Há um usuário logado.');

        $buttonCrawlerNode = $crawler->selectButton('Acessar');
        $form = $buttonCrawlerNode->form();
        $form['_username'] = 'isisbrendadaluz';
        $form['_password'] = '4GH4idMTPt';
        $client->submit($form);

        $client->followRedirects();
        $this->assertBrowserHasCookie('cnpj','/',null, 'O cookie de CNPJ não foi definido para o funcionário.');
        $this->assertRouteSame('home', [], 'Redirecionado para a rota incorreta.');
        $this->assertResponseIsSuccessful('Login não concluído');
    }
}
