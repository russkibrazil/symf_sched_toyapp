<?php

namespace App\Tests\Crawler\Controller;

use App\Repository\AgendamentoPagamentoRequestRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use DateTime;
use Symfony\Component\BrowserKit\Cookie;
use \App\Repository\AgendamentoRepository;
use \App\Repository\PerfilRepository;

class AgendamentoPagamentoControllerTest extends WebTestCase
{

    public function setup(): void
    {

    }

    public function testIndex(): void
    {
        $this->markTestIncomplete('Check usage before start working here');
    }

    public function testNew(): void
    {
        $this->markTestIncomplete('Check usage before start working here');
    }

    public function testModalPaymentForOnPlace()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilRepository::class);
        $agendamentoRepository = static::getcontainer()->get(AgendamentoRepository::class);

        $testUser = $userRepository->find('isisbrendadaluz');
        /**
         * @var \App\Entity\Agendamento $testAgendamento
         */
        $testAgendamentoArr = $agendamentoRepository->findBy([
            'cancelado' => null,
            'pagamentoPendente' => true,
            'pagamentoPresencial' => true,
            'empresa' => 38260851000146,
        ]);
        /** @var \App\Entity\Agendamento $testAgendamento */
        $testAgendamento = $testAgendamentoArr[0];
        $client->loginUser($testUser);

        $client->xmlHttpRequest('POST', sprintf('/agendamentos/index/chega/%s', $testAgendamento->getId()), []);
        $client->xmlHttpRequest('POST', sprintf('/agendamentos/index/conclui/%s', $testAgendamento->getId()), []);
        $client->xmlHttpRequest(
            'POST',
            sprintf('/agendamentos/%s/pagamento/ajax', $testAgendamento->getId()),
            [],
            [],
            [],
            json_encode([
                'fp' => 'DINTEST',
                'valor' => '100'
            ])
        );

        $this->assertResponseIsSuccessful();
    }

    public function testPaymentRequest(): void
    {
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilRepository::class);
        $agendamentoRepository = static::getcontainer()->get(AgendamentoRepository::class);

        $testUser = $userRepository->find('isisbrendadaluz');

        /**
         * @var \App\Entity\Agendamento $testAgendamento
         */
        $testAgendamentoArr = $agendamentoRepository->findBy([
            'cancelado' => null,
            'pagamentoPendente' => true,
            'pagamentoPresencial' => false,
            'empresa' => 38260851000146
        ]);
        /** @var \App\Entity\Agendamento $testAgendamento */
        $testAgendamento = $testAgendamentoArr[0];

        $client->loginUser($testUser);

        $client->xmlHttpRequest('POST', sprintf('/agendamentos/index/chega/%s', $testAgendamento->getId()), []);
        $client->xmlHttpRequest('POST', sprintf('/agendamentos/index/conclui/%s', $testAgendamento->getId()), []);
        $client->xmlHttpRequest('POST', sprintf('/agendamentos/%s/pagamento/request', $testAgendamento->getId()), []);

        $this->assertResponseIsSuccessful();
    }

    public function testInavlidPaymentRequest(): void
    {
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilRepository::class);
        $agendamentoRepository = static::getcontainer()->get(AgendamentoRepository::class);

        $testUser = $userRepository->find('isisbrendadaluz');

        /**
         * @var \App\Entity\Agendamento $testAgendamento
         */
        $testAgendamentoArr = $agendamentoRepository->findBy([
            'cancelado' => null,
            'pagamentoPendente' => true,
            'pagamentoPresencial' => false,
            'empresa' => 38260851000146
        ]);
        /** @var \App\Entity\Agendamento $testAgendamento */
        $testAgendamento = $testAgendamentoArr[0];

        $client->loginUser($testUser);

        for ($i=0; $i < 2; $i++) {
            $client->xmlHttpRequest('POST', sprintf('/agendamentos/index/chega/%s', $testAgendamento->getId()), []);
            $client->xmlHttpRequest('POST', sprintf('/agendamentos/index/conclui/%s', $testAgendamento->getId()), []);
            $client->xmlHttpRequest('POST', sprintf('/agendamentos/%s/pagamento/request', $testAgendamento->getId()), []);
        }

        $this->assertResponseIsUnprocessable();
    }

    public function testPaymentSelector(): void
    {
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $client->followRedirects();
        $userRepository = static::getcontainer()->get(PerfilRepository::class);
        $agendamentoRepository = static::getcontainer()->get(AgendamentoRepository::class);

        $testUser = $userRepository->find('isisbrendadaluz');

        /**
         * @var \App\Entity\Agendamento $testAgendamento
         */
        $testAgendamentoArr = $agendamentoRepository->findBy([
            'cancelado' => null,
            'pagamentoPendente' => true,
            'pagamentoPresencial' => false,
            'empresa' => 38260851000146
        ]);
        /** @var \App\Entity\Agendamento $testAgendamento */
        $testAgendamento = $testAgendamentoArr[0];

        $client->loginUser($testUser);

        $client->xmlHttpRequest('POST', sprintf('/agendamentos/index/chega/%s', $testAgendamento->getId()), []);
        $client->xmlHttpRequest('POST', sprintf('/agendamentos/index/conclui/%s', $testAgendamento->getId()), []);
        $client->xmlHttpRequest('POST', sprintf('/agendamentos/%s/pagamento/request', $testAgendamento->getId()), []);

        $paymentRequest = self::getContainer()->get(AgendamentoPagamentoRequestRepository::class)->findOneBy([
            'agendamento' => $testAgendamento
        ]);

        $this->assertNotNull($paymentRequest);


        $testUser = $testAgendamento->getCliente();
        $client->loginUser($testUser);

        $crawler = $client->request('GET', sprintf('/agendamentos/%s/pagamento/select?prid=%s', $testAgendamento->getId(), $paymentRequest->getToken()));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'pagamento');
    }

    public function testEstorno(): void
    {
        $this->markTestIncomplete('WIP');

        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilRepository::class);
        $agendamentoRepository = static::getcontainer()->get(AgendamentoRepository::class);

        $testUser = $userRepository->find('isisbrendadaluz');
        /**
         * @var \App\Entity\Agendamento $testAgendamento
         */
        $testAgendamentoArr = $agendamentoRepository->findBy([
            'cancelado' => null,
            'pagamentoPendente' => true,
            'pagamentoPresencial' => false,
            'empresa' => 38260851000146
        ]);
        /** @var \App\Entity\Agendamento $testAgendamento */
        $testAgendamento = $testAgendamentoArr[0];
        $client->loginUser($testUser);

        $client->xmlHttpRequest('POST', sprintf('/agendamentos/index/conclui/%s', $testAgendamento->getId()), []);
    }

    public function testReembolso(): void
    {
        $this->markTestIncomplete('WIP');

        /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = static::getcontainer()->get(PerfilRepository::class);
        $agendamentoRepository = static::getcontainer()->get(AgendamentoRepository::class);

        $testUser = $userRepository->find('isisbrendadaluz');
        /**
         * @var \App\Entity\Agendamento $testAgendamento
         */
        $testAgendamentoArr = $agendamentoRepository->findBy([
            'cancelado' => null,
            'pagamentoPendente' => true,
            'pagamentoPresencial' => false,
            'empresa' => 38260851000146
        ]);
        /** @var \App\Entity\Agendamento $testAgendamento */
        $testAgendamento = $testAgendamentoArr[0];
        $client->loginUser($testUser);
    }
}
