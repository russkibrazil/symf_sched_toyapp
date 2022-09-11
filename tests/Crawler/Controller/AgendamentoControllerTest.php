<?php

namespace App\Tests\Crawler\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use DateTime;
use Symfony\Component\BrowserKit\Cookie;
use App\Repository\AgendamentoRepository;
use App\Repository\PerfilClienteRepository;
use App\Repository\PerfilFuncionarioRepository;
use App\Repository\PerfilRepository;
use App\Repository\ServicoRepository;

class AgendamentoControllerTest extends WebTestCase
{
    /**
     * @dataProvider userProvider
     * @return void
     * @see https://symfony.com/doc/current/components/phpunit_bridge.html#clock-mocking
     */
    public function testAgendamentoIndex(string $fqrcn, string $nomeUsuario, int $regEsperados): void
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get($fqrcn);
        $testUser = $userRepository->find($nomeUsuario);
        $client->loginUser($testUser);
        $client->followRedirects();
        $crawler = $client->request('GET', '/agendamentos');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Agendamentos futuros');
        $regs = $crawler->filter('button.btn-chegada');
        $this->assertEquals($regEsperados, count($regs));
    }

    public function userProvider(): array
    {
        return [
            'teste_funcionario' => [PerfilFuncionarioRepository::class, 'jorgerenangalvao', 3],
            'teste_cliente' => [PerfilClienteRepository::class, 'rodrigofranciscoviana', 3]
        ];
    }

    /**
     * @dataProvider pendenteUserProvider
     */
    public function testAgendamentoPendentes(string $nomeUsuario, int $regEsperados): void
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find($nomeUsuario);
        $client->loginUser($testUser);
        $client->followRedirects();
        $crawler = $client->request('GET', '/agendamentos/pendente');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Agendamentos pendentes');
        $regs = $crawler->filter('button.btn-pagar');
        $this->assertEquals($regEsperados, count($regs));
    }

    public function pendenteUserProvider(): array
    {
        return [
           'admin' => ['isisbrendadaluz', 3],
           'caixa' => ['victorpviana', 3],
        ];
    }

    /**
     * Testing the route Agendamento::Pagos
     *
     * @dataProvider pagoUserProvider
     * @param string $nomeUsuario
     * @param integer $regEsperados
     * @return void
     */
    public function testAgendamentoPagos(string $nomeUsuario, int $regEsperados): void
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find($nomeUsuario);
        $client->loginUser($testUser);
        $client->followRedirects();
        $crawler = $client->request('GET', '/agendamentos/pago');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Agendamentos encerrados');
        $regs = $crawler->filter('button.btn-reembolsar');
        $this->assertEquals($regEsperados, count($regs));
    }

    public function pagoUserProvider(): array
    {
        return [
            'admin' => ['isisbrendadaluz', 2],
            'caixa' => ['victorpviana', 2],
        ];
    }

    public function testAgendamentoNew(): void
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $client->loginUser($testUser);
        $client->followRedirects();

        $crawler = $client->request('GET', '/agendamentos/new');
        $this->assertSelectorTextSame('h1', 'Criar um agendamento', 'Página não carregada');

        $saveButton = $crawler->filter('form button.btn-success')->first();
        $clientSearchInput = $crawler->filter('#agendamento_pesquisa_cliente')->first();

        $this->assertEquals('Salvar', $saveButton->innerText(), 'Save Button text not expected');
        $this->assertNull($clientSearchInput->attr('disabled'), 'Client search input is disabled');

        /** @var \App\Entity\PerfilCliente $scheduleClient */
        $scheduleClient = static::getcontainer()->get(PerfilClienteRepository::class)->find('rodrigofranciscoviana');
        /** @var \App\Entity\PerfilFuncionario $worker */
        $worker = self::getContainer()->get(PerfilFuncionarioRepository::class)->find('jorgerenangalvao');
        $service = self::getContainer()->get(ServicoRepository::class)->findOneBy(['servico' => 'Corte Simples']);

        // https://stackoverflow.com/questions/15454760/symfony2-test-on-arraycollection-gives-unreachable-field
        $form = $saveButton->form();

        $formValues = $form->getPhpValues();
        $formValues['agendamento']['horario'] = date('Y-m-d H:i:s');
        $formValues['agendamento']['formaPagto'] = 'DINHEIRO';
        $formValues['agendamento']['cpf'] = $scheduleClient->getUserIdentifier();
        $formValues['agendamento']['funcionario'] = $worker->getUserIdentifier();
        $formValues['agendamento']['servicos'][0]['servico'] = $service->getId();

        $client->submit($form, $formValues);

        $this->assertResponseIsSuccessful('Something went wrong');
        $this->assertRouteSame('agendamentos_index', [], 'Redirected to unexpected route');
    }

    public function testAtrasarAgendamento()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilClienteRepository::class);
        $agendamentoRepository = static::getcontainer()->get(AgendamentoRepository::class);
        /**
         * @var \App\Entity\PerfilCliente $testUser
         */
        $testUser = $userRepository->find('rodrigofranciscoviana');

        /**
         * @var \App\Entity\ClienteAvaliacao
         */
        $testUserRep = $testUser
            ->getUsuarioReputacao()
            ->filter(function ($el)
            {
                return $el->getCnpj()->getCnpj() == 38260851000146;
            })
           ->first()
        ;
        $atrasoInicial = $testUserRep->getAtrasos();

        $testAgendamento = $agendamentoRepository->findOneBy([
            'cliente' => 'rodrigofranciscoviana',
            'funcionario' => 'jorgerenangalvao',
            'empresa' => 38260851000146,
            'horario' => new DateTime('2021-12-22 11:30:00')
        ]);
        $client->loginUser($testUser);

        $client->xmlHttpRequest('POST', sprintf('/agendamentos/index/atraso/%s', $testAgendamento->getId()), []);
        $this->assertResponseIsSuccessful('Agendamento não encontrado');

        $testAgendamentoA = $agendamentoRepository->findOneBy([
            'cliente' => 'rodrigofranciscoviana',
            'funcionario' => 'jorgerenangalvao',
            'empresa' => 38260851000146,
            'horario' => new DateTime('2021-12-22 11:40:00')
        ]);

        $this->assertNotEquals(null, $testAgendamentoA, 'O atraso não foi registrado');
        $this->assertGreaterThan($atrasoInicial, $testUserRep->getAtrasos(), 'Não foi contabilizado o atraso na ficha do cliente.');
    }

    public function testCancelarAgendamento()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilClienteRepository::class);
        $agendamentoRepository = static::getcontainer()->get(AgendamentoRepository::class);
        /**
         * @var \App\Entity\PerfilCliente $testUser
        */
        $testUser = $userRepository->find('rodrigofranciscoviana');
        $client->loginUser($testUser);

        /**
         * @var \App\Entity\ClienteAvaliacao
        */
        $testUserRep = $testUser
            ->getUsuarioReputacao()
            ->filter(function ($el)
            {
                return $el->getCnpj()->getCnpj() == 38260851000146;
            })
        ->first()
        ;

        $testAgendamento = $agendamentoRepository->findOneBy([
            'cliente' => 'rodrigofranciscoviana',
            'funcionario' => 'jorgerenangalvao',
            'empresa' => 38260851000146,
            'horario' => new DateTime('2021-12-22 11:30:00')
        ]);
        $this->assertNotNull($testAgendamento);

        $cancelamentoInicial = $testUserRep->getCancelamentos();

        $client->xmlHttpRequest('POST', sprintf('/agendamentos/index/cancela/$s', $testAgendamento->getId()), [], [], [], '{"reason":"Test","reason_description":"Test reason description"}');
        $this->assertResponseIsSuccessful('Cancelling not registered: ' . $testAgendamento->getId());

        $testUserA = $userRepository->find('rodrigofranciscoviana');

        $testUserRep = $testUserA
            ->getUsuarioReputacao()
            ->filter(function ($el)
            {
                return $el->getCnpj()->getCnpj() == 38260851000146;
            })
            ->first()
        ;
        $this->assertNotSame(null, $testAgendamento->getCancelado(), 'O cancelamento não foi registrado');
        $this->assertGreaterThan($cancelamentoInicial, $testUserRep->getCancelamentos(), 'Não foi contabilizado o cancelamento na ficha do cliente.');
    }

    public function testChegar()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $agendamentoRepository = static::getcontainer()->get(AgendamentoRepository::class);

        $testUser = $userRepository->find('isisbrendadaluz');
        /**
         * @var \App\Entity\Agendamento $testAgendamento
         */
        $testAgendamentoArr = $agendamentoRepository->findBy([
            'cliente' => 'rodrigofranciscoviana',
            'funcionario' => 'jorgerenangalvao',
            'empresa' => 38260851000146,
            'horario' => new DateTime('2021-12-22 11:30:00')
        ]);
        /** @var \App\Entity\Agendamento $testAgendamento */
        $testAgendamento = $testAgendamentoArr[0];
        $client->loginUser($testUser);
        $client->xmlHttpRequest('POST', sprintf('/agendamentos/index/chega/%s', $testAgendamento->getId()), []);
        $this->assertResponseIsSuccessful('Agendamento não encontrado');
        $this->assertTrue($testAgendamento->getCompareceu(), 'Alteração não registrada');
    }

    public function testConcluirAgendamento()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $agendamentoRepository = static::getcontainer()->get(AgendamentoRepository::class);

        $testUser = $userRepository->find('isisbrendadaluz');
        /**
         * @var \App\Entity\Agendamento $testAgendamento
         */
        $testAgendamentoArr = $agendamentoRepository->findBy([
            'cliente' => 'rodrigofranciscoviana',
            'funcionario' => 'jorgerenangalvao',
            'empresa' => 38260851000146,
            'horario' => new DateTime('2021-12-22 11:30:00')
        ]);
        /** @var \App\Entity\Agendamento $testAgendamento */
        $testAgendamento = $testAgendamentoArr[0];
        $client->loginUser($testUser);
        $client->xmlHttpRequest('POST', sprintf('/agendamentos/index/chega/%s', $testAgendamento->getId()), []);
        $client->xmlHttpRequest('POST', sprintf('/agendamentos/index/conclui/%s', $testAgendamento->getId()), []);
        $this->assertResponseIsSuccessful('Agendamento não encontrado');

        $testAgendamentoArr = $agendamentoRepository->findBy([
            'cliente' => 'rodrigofranciscoviana',
            'funcionario' => 'jorgerenangalvao',
            'empresa' => 38260851000146,
            'horario' => new DateTime('2021-12-22 11:30:00')
        ]);
        /** @var \App\Entity\Agendamento $testAgendamento */
        $testAgendamento = $testAgendamentoArr[0];
        $this->assertTrue($testAgendamento->getConcluido(), 'Alteração não registrada');
    }

    public function testEdit()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $client->followRedirects();
        $userRepository = static::getcontainer()->get(PerfilFuncionarioRepository::class);
        $agendamentoRepository = static::getcontainer()->get(AgendamentoRepository::class);

        $testUser = $userRepository->find('isisbrendadaluz');
        /**
         * @var \App\Entity\Agendamento $testAgendamento
         */
        $testAgendamentoArr = $agendamentoRepository->findBy([
            'cliente' => 'rodrigofranciscoviana',
            'funcionario' => 'jorgerenangalvao',
            'empresa' => 38260851000146,
            'horario' => new DateTime('2021-12-22 11:30:00')
        ]);
        /** @var \App\Entity\Agendamento $testAgendamento */
        $testAgendamento = $testAgendamentoArr[0];
        $client->loginUser($testUser);

        $crawler = $client->request('GET', sprintf('/agendamentos/%s/edit', $testAgendamento->getId()));

        $saveButton = $crawler->filter('form button.btn-success')->first();
        $clientSearchInput = $crawler->filter('#agendamento_pesquisa_cliente')->first();

        $this->assertEquals('Atualizar', $saveButton->innerText(), 'Save Button text not expected');
        $this->assertNotNull($clientSearchInput->attr('disabled'), 'Client search input is disabled');

        $form = $saveButton->form();
        $form['agendamento[formaPagto]'] = 'DINHEIRO';

        $client->submit($form);

        $this->assertResponseIsSuccessful('Something went wrong');
        $this->assertRouteSame('agendamentos_index', [], 'Redirected to unexpected route');
    }
}
