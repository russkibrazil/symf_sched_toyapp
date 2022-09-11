<?php

namespace App\Tests\EventSubscriber;

use App\Repository\RegistroAcessoRepository;
use App\Repository\PerfilRepository;
use Symfony\Component\Panther\PantherTestCase;

class LoginConcedidoSubscriberTest extends PantherTestCase
{
    public function testRegistroAcesso(): void
    {
        /** @var Symfony\Component\BrowserKit\AbstractBrowser $client */
        $client = static::createClient();
        $client->followRedirects();
        $usuario = 'isisbrendadaluz';
        $userRepository = static::getContainer()->get(PerfilRepository::class);
        /**
         * @var \App\Repository\RegistroAcessoRepository $regAcessoRepository
         */
        $regAcessoRepository = static::getContainer()->get(RegistroAcessoRepository::class);
        $em = self::getContainer()->get('doctrine')->getManager();

        foreach ($regAcessoRepository->findAll() as $object) {
            $em->remove($object);
        }
        $em->flush();

        $u = $userRepository->find($usuario);
        $crawler = $client->request('GET', '/login');
        $buttonCrawlerNode = $crawler->selectButton('Acessar');
        $form = $buttonCrawlerNode->form();
        $form['_username'] = $usuario;
        $form['_password'] = '4GH4idMTPt';
        $client->submit($form);

        $regs = $regAcessoRepository->findBy(['usuario' => $u]);
        $this->assertGreaterThan(0, count($regs));
    }

    // public function testAceitePrivacidade(): void
    // {
    //     $usuario = 'ricardovictoreliasgoncalves__ricardovictoreliasgoncalves@acaocontabilsjc.com.br';
    //     $senha = 'XmlrnMOHRP';

    //     $client = static::createPantherClient();
    //     $client->manage()->deleteAllCookies();
    //     $crawler = $client->request('GET', '/login');

    //     $form = $crawler->selectButton('Entrar')->form();
    //     $form['_username'] = $usuario;
    //     $form['_password'] = $senha;
    //     $client->submit($form);

    //     // teste de recusa
    //     $crawler = $client->refreshCrawler();
    //     $this->assertSelectorTextContains('h1', 'Novos termos de privacidade');
    //     $client->executeScript('document.getElementById("form_lido").click()');
    //     // $this->assertNull($crawler->filter('button[type=submit]')->attr('disabled'));
    //     $this->assertEquals('', $crawler->filter('button[type=submit]')->attr('disabled'));
    //     $client->executeScript('document.querySelector("body button[type=button]").click()');

    //     $crawler = $client->refreshCrawler();
    //     $this->assertSelectorTextContains('h1', 'Login Área Cliente');
    //     $form = $crawler->selectButton('Entrar')->form();
    //     $form['_username'] = $usuario;
    //     $form['_password'] = $senha;
    //     $client->submit($form);

    //     // teste de aceite
    //     $crawler = $client->refreshCrawler();
    //     $this->assertSelectorTextContains('h1', 'Novos termos de privacidade');
    //     $client->executeScript('document.getElementById("form_lido").click()');
    //     $client->executeScript('document.querySelector("button[type=submit]").click()');
    //     $this->assertSelectorTextContains('h1', 'Dashboard');

    //     $crawler = $client->request('GET', '/logout');
    //     $form = $crawler->selectButton('Entrar')->form();
    //     $form['_username'] = $usuario;
    //     $form['_password'] = $senha;
    //     $client->submit($form);
    //     $this->assertSelectorTextContains('h1', 'Dashboard');
    // }
}
