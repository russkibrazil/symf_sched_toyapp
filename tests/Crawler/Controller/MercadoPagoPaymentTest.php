<?php

namespace App\Tests\Crawler\Controller;

use App\Entity\Agendamento;
use App\Entity\AgendamentoPagamento;
use App\Entity\Empresa;
use App\Entity\Perfil;
use App\Entity\PerfilCliente;
use App\Entity\Servico;
use App\Entity\AgendamentoServicos;
use App\Repository\AgendamentoRepository;
use MercadoPago;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MercadoPagoPaymentTest extends WebTestCase
{
    private string $scheduleUid;
    private PerfilCliente $clientProfile;
    private float $value;
    private AgendamentoRepository $scheduleRepository;
    private const MERPAGO_CPF = '12345678909';
    private const MERPAGO_EMAIL_CREDIT = 'test_user_53460870@testuser.com';
    private const MERPAGO_EMAIL_TEST = 'test_user_76526010@testuser.com';
    private const MERPAGO_NAME_PAYMENT_APPROVED = 'APRO';
    private const MERPAGO_NAME_PAYMENT_ERROR = 'OTHE';
    private const MERPAGO_NAME_PAYMENT_PENDING = 'CONT';
    private const MERPAGO_NAME_PAYMENT_CALL_TO_APPROVE = 'CALL';
    private const MERPAGO_NAME_PAYMENT_NO_FUNDS = 'FUND';
    private const MERPAGO_NAME_PAYMENT_INVALID_CVV = 'SECU';
    private const MERPAGO_NAME_PAYMENT_EXPIRED_CARD = 'EXPI';
    private const MERPAGO_NAME_PAYMENT_FORM_ERROR = 'FROM';
    private const MERPAGO_MASTERCARD_ISSUER_ID = '24';
    private const MERPAGO_PAYMENT_METHOD_MASTERCARD = 'master';

    public function setup(): void
    {
        static::bootKernel();
        $doctrine = static::getContainer()->get('doctrine');
        $this->scheduleRepository = $doctrine->getRepository(Agendamento::class);
        $pessoaRepository = $doctrine->getRepository(Perfil::class);
        $service = $doctrine->getRepository(Servico::class)->findOneBy(['servico' => 'Serviço Symfony']);
        $company = $doctrine->getRepository(Empresa::class)->find('38260851000146');
        $this->clientProfile = $pessoaRepository->find('rodrigofranciscoviana');
        $employee = $pessoaRepository->find('jorgerenangalvao');

        $scheduleToRefund = (new Agendamento())
            ->setHorario(date('c', time()))
            ->setCompareceu(true)
            ->setFormaPagto('DINHEIRO')
            ->setEmpresa($company)
            ->setCliente($this->clientProfile)
            ->setFuncionario($employee)
            ->setConclusaoEsperada(date('c', time()+65*60))
            ->setPagamentoPresencial(false)
            ->addServico((new AgendamentoServicos())
                ->setServico($service));

        $scheduleToCancelPayment = (new Agendamento())
            ->setHorario(date('c', time()-30*60))
            ->setCompareceu(true)
            ->setFormaPagto('DINHEIRO')
            ->setEmpresa($company)
            ->setCliente($this->clientProfile)
            ->setFuncionario($employee)
            ->setConclusaoEsperada(date('c', time()+35*60))
            ->setPagamentoPresencial(false)
            ->addServico((new AgendamentoServicos())
                ->setServico($service));


        $em = $doctrine->getManager();
        $em->persist($scheduleToRefund);
        $em->persist($scheduleToCancelPayment);
        $em->flush();

        $this->scheduleUid = $scheduleToRefund->getId();
        $this->value = (float) $service->getValor();
    }

    // https://www.mercadopago.com.br/developers/pt/docs/testing/test-cards
    // MASTERCARD ISSUER ID = 24
    // VISA ISSUER ID = 25
    public function testCardPayment(): void
    {
        MercadoPago\SDK::setAccessToken($_ENV['MERCADOPAGO_SECRET']);
        $cardToken = new MercadoPago\CardToken([
            'public_key' => $_ENV('MERCADOPAGO_PUBLIC'),
            'first_six_digits' => '503143',
            'last_four_digits' => '6351',
            'card_number_length' => 3,
            'security_code_length' => 3,
            'expiration_year' => 2025,
            'expiration_month' => 12,
            'card_number_length' => 12
        ]);
        $cardToken->save();

        $client = static::createClient();
        $crawler = $client->request(
            'POST',
            sprintf('/agendamentos/%d/pagamento/mp/card/validate', $this->scheduleUid),
            [],
            [],
            [],
            json_encode([
                'transaction_amount' => $this->value,
                'token' => $cardToken->id,
                'description' => 'Pagamento de serviços via Iroko',
                'installments' => 2,
                'payment_method_id' => self::MERPAGO_PAYMENT_METHOD_MASTERCARD,
                'issuer_id' => self::MERPAGO_MASTERCARD_ISSUER_ID,
                'payer' => [
                    'email' => self::MERPAGO_EMAIL_TEST,
                    'identification' => [
                        'type' => 'CPF',
                        'number' => self::MERPAGO_CPF
                    ]
                ]
            ]));

        $this->assertResponseIsSuccessful();

        // TODO test hook data modification

    }

    public function testRefundPayment()
    {
        $client = static::createClient();
        $crawler = $client->request(
            'POST',
            sprintf('/agendamentos/%d/pagamento/refund', $this->scheduleUid),
            [],
            [],
            [],
            json_encode([
                'valor_reembolso' => $this->value
            ])
        );

        $this->assertResponseIsSuccessful();

        // check if agendamentoPagamento is 'refunded'
        $schedulePaymentInfo = (self::getContainer()->get('doctrine'))->getRepository(AgendamentoPagamento::class)->findBy(['agendamento' => $this->scheduleUid])[0];
        $this->assertNotNull($schedulePaymentInfo);
        $this->assertEquals('refunded', $schedulePaymentInfo->getStatusAtual(), 'Payment status not updated');
    }


    function testCancelPayment()
    {
        $client = static::createClient();
        $crawler = $client->request(
            'POST',
            sprintf('/agendamentos/%d/pagamento/request/cancel', $this->scheduleUid)
        );

        $this->assertResponseIsSuccessful();

        // TODO check if agendamentoPagamento is 'cancelled'

        // TODO test hook data modification

    }
}