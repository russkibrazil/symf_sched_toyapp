<?php

namespace App\Controller;

use DateTime;
use MercadoPago;
use Knp\Snappy\Pdf;
use App\Entity\Empresa;
use App\Entity\Agendamento;
use App\Entity\AgendamentoPagamento;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->doctrine = $managerRegistry;
    }
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request)
    {
        return $this->render('home/index.html.twig', []);
    }

    /**
     * Rota para seleção de empresa para clientes
     * @Route("/initial_select", name="empresa_selecao_inicial", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_USER') or is_granted('ROLE_PROPRIETARIO')")
     * @param Request $request
     * @return Response
     */
    public function selectEmpresa(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('empresa', EntityType::class, [
                'label' => 'Empresa',
                'class' => Empresa::class,
                'choice_label' => 'nomeEmpresa',
            ])
            ->getForm()
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $cnpj = $form->get('empresa')->getViewData();
            $response = $this->redirectToRoute('home');
            $newCookie = new Cookie('cnpj', $cnpj);
            $response->headers->setCookie($newCookie);
            return $response;
        }
        return $this->render('configuracao/select_empresa.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mp/hook", name="mp-hook", methods={"POST"})
     */
    public function mpHook(Request $request, HttpClientInterface $httpClient, MailerInterface $mailer, Pdf $pdf): Response
    {
        MercadoPago\SDK::setAccessToken($_ENV['MERCADOPÁGO_PRIVATE']);
        $requestBody = $request->toArray();
        $queryParams = $request->query->all();
        if ($requestBody['type'] == 'test')
            return new Response('', 201);
        $idMP = $requestBody['data']['id'] ?? $queryParams['id'];
        if ($idMP == null)
            return new Response('', 400);

        // WEBHOOKS
        if (count($requestBody) > 0)
        {
            switch($requestBody['type']) {
                case "payment":
                    $payment = MercadoPago\Payment::find_by_id($idMP);
                    $this->processPayment($requestBody, $payment, $mailer, $pdf);
                    break;
                case "plan":
                    // $plan = MercadoPago\Plan::find_by_id($idMP);
                    // break;
                case "subscription":
                    // $plan = MercadoPago\Subscription::find_by_id($idMP);
                    // break;
                case "invoice":
                    // $plan = MercadoPago\Invoice::find_by_id($idMP);
                    // break;
                case "point_integration_wh":
                    // $_POST contém as informações relacionadas à notificação.
                    return new Response();
                    break;
                default:
                    return new Response('', 400);
            }

            return new Response();
        }

        // IPN
        if (count($queryParams) > 0)
        {
            switch($queryParams["topic"]) {
                case "payment":
                    $payment = MercadoPago\Payment::find_by_id($queryParams["id"]);
                    $this->processPayment($requestBody, $payment, $mailer, $pdf);
                    break;
                case "chargebacks":
                    // contestação de pagamento
                    $response = $httpClient->request(
                        'GET',
                        'https://api.mercadopago.com/v1/chargebacks/' . $queryParams['id'],
                        [
                            'auth_bearer' => $_ENV['MERCADOPAGO_SECRET']
                        ]
                    );
                    $this->processChargeback($response);
                    break;
                case "merchant_order":
                    // $merchant_order = MercadoPago\MerchantOrder::find_by_id($_GET["id"]);
                    // break;
                case "point_integration_ipn":
                   // $_POST contém as informações relacionadas à notificação.
                //    break;
                default:
                    break;
            }
            return new Response('', 200);
        }

        //NONE OF THE OTHERS
        return new Response('', 400);
    }

    private function processChargeback(ResponseInterface $response)
    {
        if ($response->getStatusCode() == 200)
        {
            $chargebackData = $response->toArray();
            /**
             * @var \App\Repository\AgendamentoPagamentoRepository $apRepo
             * @var \App\Entity\AgendamentoPagamento $pagamento
             */
            $apRepo = $this->doctrine->getRepository(AgendamentoPagamento::class);
            $pagamento = $apRepo->findIdInLog($chargebackData['payments'][0]);
            $log = $pagamento->getLog();
            $log[] = $chargebackData;
            $pagamento
                ->setLog($log)
                ->setStatusAtual('chargeback')
                ->setUltimaModificacao(new DateTime())
            ;

            $this->doctrine->getManager()->flush();
        }
    }

    public function processPayment(array $requestData, MercadoPago\Payment $payment, MailerInterface $mailer, Pdf $pdf)
    {
        /**
         * @var \App\Repository\AgendamentoPagamentoRepository $apRepo
         * @var \App\Entity\AgendamentoPagamento $pagamento
         */
        $apRepo = $this->doctrine->getRepository(AgendamentoPagamento::class);
        $pagamento = $apRepo->findIdInLog($requestData['data']['id']);

        if ($pagamento == []) return;

        if ($noChangeDetected = $pagamento->getStatusAtual() == $payment->status) return; // conferir a fundo se realmente não há nenhuma mudança
        $log = $pagamento->getLog();
        $log[] = $requestData;

        $pagamento
            ->setLog($log)
            ->setUltimaModificacao(new DateTime())
        ;

        if (($requestData['action'] ?? '') == 'payment.updated' || !$noChangeDetected)
        {
            $agendamento = $pagamento->getAgendamento();

            if ($agendamento->getPagamentoPendente() && $payment->status == 'approved')
                $agendamento->setPagamentoPendente(false);
                $this->sendInvoice($mailer, $agendamento, $pdf);

            if (!$agendamento->getPagamentoPendente() && $payment->status != 'approved')
            {
                $agendamento->setPagamentoPendente(true);
            }
        }
        $this->doctrine->getManager()->flush();

    }

    private function sendInvoice(MailerInterface $mailer, Agendamento $agendamento, Pdf $pdf)
    {
        if ($agendamento->getEmpresa()->getCnpj() != '00000000000000')
        {
            $emailHtmlRendered = $this->renderView('agendamento_pagamento/invoice/payment_summary_email.inky.twig', [
                'agendamento' => $agendamento
            ]);
            $pdf->generateFromHtml($emailHtmlRendered, 'public/invoice/' . $agendamento->getId() . '-' .  date_format(new DateTime(), 'YmdHi'), []);
            $email = (new TemplatedEmail())
                ->from('no-reply@oddboxmedia.com.br')
                ->to($agendamento->getCliente()->getEmail())
                ->subject('Iroko - Comprovante de pagamento')
                ->html($emailHtmlRendered)
            ;
            $mailer->send($email);
        }
    }
}
