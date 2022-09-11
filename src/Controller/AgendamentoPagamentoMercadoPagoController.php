<?php

namespace App\Controller;

use App\Entity\Agendamento;
use App\Entity\AgendamentoPagamento;
use App\Entity\EmpresaProcessadorPagamento;
use App\Form\AgendamentoPagamentoCartaoType;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Snappy\Pdf;
use MercadoPago;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Undocumented class
 * @Route("/agendamentos/{id}/pagamento/mp")
 * @IsGranted("ROLE_USER")
 */
class AgendamentoPagamentoMercadoPagoController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $mr)
    {
        $this->doctrine = $mr;
    }

    /**
     * @Route("/card", name="agendamento_pagamento_payment_mercadopago_card", methods={"GET", "POST"})
     */
    public function payByCardMercadoPago(Request $request, UrlGeneratorInterface $urlGen, Agendamento $agendamento): Response
    {
        $pproc = $this->doctrine->getRepository(EmpresaProcessadorPagamento::class)->findOneBy(['processador' => 'MERPAGO']);
        if ($pproc === null) {
            return $this->createNotFoundException('This payment processor is not configured.');
        }

        $form = $this->createForm(AgendamentoPagamentoCartaoType::class, null);
        $form->handleRequest($request);

        return $this->render('agendamento_pagamento/creditcard.html.twig', [
            'form' => $form->createView(),
            'public_key' => $_ENV['MERCADOPAGO_PUBLIC'],
            'route_process' => $urlGen->generate('agendamento_pagamento_payment_mercadopago_validation', ['id' => $agendamento->getId()]),
            'form_name' => $form->getName(),
            'total_value' => $this->calculaValorTotalAgendamento($agendamento),
            'descricao_pagamento' => 'Pagamento de serviços via Iroko',
            'auto_redirect' => $request->headers->has('referer'),
            'has_pix' => ($pproc->getPix() ?? '') != '' ? $urlGen->generate('agendamento_pagamento_payment_mercadopago_pix', ['id' => $agendamento->getId()]) : null,
        ]);
    }

    /**
     * @Route("/card/validate", name="agendamento_pagamento_payment_mercadopago_validation", methods={"POST"})
     */
    public function validateCardPaymentMercadoPago(Request $request, Agendamento $agendamento, MailerInterface $mailer, Pdf $pdf): JsonResponse
    {
        try {
            $parsed_body = json_decode($request->getContent(), true);

            MercadoPago\SDK::setAccessToken($_ENV['MERCADOPAGO_SECRET']);

            $payment = new MercadoPago\Payment();
            $payment->transaction_amount = $parsed_body['transaction_amount'];
            $payment->token = $parsed_body['token'];
            $payment->description = $parsed_body['description'];
            $payment->installments = $parsed_body['installments'];
            $payment->payment_method_id = $parsed_body['payment_method_id'];
            $payment->issuer_id = $parsed_body['issuer_id'];

            $payer = new MercadoPago\Payer();
            $payer->email = $parsed_body['payer']['email'];
            $payer->identification = array(
                "type" => $parsed_body['payer']['identification']['type'],
                "number" => $parsed_body['payer']['identification']['number'],
            );
            $payment->payer = $payer;

            $payment->save();

            $this->validate_payment_result($payment);

            $response_fields = array(
                'id' => $payment->id,
                'status' => $payment->status,
                'detail' => $payment->status_detail,
            );

            if (in_array($payment->status, ['approved', 'in_process'])) {
                $flashMessage = 'You missed the target.';
                switch ($payment->status_detail) {
                    case 'accredited':
                        $flashMessage = 'Pronto, seu pagamento foi aprovado! No resumo, você verá a cobrança do valor como "' . $payment->description . '".';
                        $agendamento
                            ->setPagamentoPendente(false)
                            ->setConcluido(true)
                        ;
                        $this->sendInvoice($mailer, $agendamento, $pdf);
                        break;
                    case 'pending_contingency':
                        $flashMessage = 'Estamos processando o pagamento. Não se preocupe, em menos de 2 dias úteis informaremos por e-mail se foi creditado.';
                        break;
                    case 'pending_review_manual':
                        $flashMessage = 'Estamos processando seu pagamento. Não se preocupe, em menos de 2 dias úteis informaremos por e-mail se foi creditado ou se necessitamos de mais informação.';
                        break;
                    default:
                        break;
                }
                $this->addFlash('sucesso', $flashMessage);
                $response = new JsonResponse($response_fields, 201);

                $reg = (new AgendamentoPagamento())
                    ->setAgendamento($agendamento)
                    ->setCapturado($payment->capture ?? true)
                    ->setFormaPagto('CARTAO')
                    ->setProcessador('MERPAGO')
                    ->setStatusAtual($payment->status)
                    ->setValor($payment->transaction_amount)
                    ->setLog($response_fields)
                ;

                $em = $this->doctrine->getManager();
                $em->persist($reg);
                $em->flush();
            } else {
                $response_error = [];
                switch ($payment->status_detail) {
                    case 'cc_rejected_bad_filled_card_number':
                        $response_error['field_id'] = 'cardNumber';
                        $response_error['message'] = 'Revise o número do cartão.';
                        break;
                    case 'cc_rejected_bad_filled_date':
                        $response_error['field_id'] = 'cardExpirationYear';
                        $response_error['message'] = 'Revise a data de vencimento.';
                        break;
                    case 'cc_rejected_bad_filled_other':
                        $response_error['field_id'] = '';
                        $response_error['message'] = 'Revise os dados.';
                        break;
                    case 'cc_rejected_bad_filled_security_code':
                        $response_error['field_id'] = 'cardNumber';
                        $response_error['message'] = 'Revise o código de segurança do cartão.';
                        break;
                    case 'cc_rejected_blacklist':
                        $response_error['field_id'] = '';
                        $response_error['message'] = 'Não pudemos processar seu pagamento.';
                        break;
                    case 'cc_rejected_call_for_authorize':
                        $response_error['field_id'] = '';
                        $response_error['message'] = 'Você deve autorizar ao ' . $payment->payment_method_id . ' o pagamento do valor ao Mercado Pago.';
                        break;
                    case 'cc_rejected_card_disabled':
                        $response_error['field_id'] = '';
                        $response_error['message'] = 'Ligue para o payment_method_id para ativar seu cartão. O telefone está no verso do seu cartão.';
                        break;
                    case 'cc_rejected_card_error':
                        $response_error['field_id'] = 'cardNumber';
                        $response_error['message'] = 'Não conseguimos processar seu pagamento.';
                        break;
                    case 'cc_rejected_duplicated_payment':
                        $response_error['field_id'] = '';
                        $response_error['message'] = 'Você já efetuou um pagamento com esse valor. Caso precise pagar novamente, utilize outro cartão ou outra forma de pagamento.';
                        break;
                    case 'cc_rejected_high_risk':
                        $response_error['field_id'] = '';
                        $response_error['message'] = 'Seu pagamento foi recusado. Escolha outra forma de pagamento. Recomendamos meios de pagamento em dinheiro.';
                        break;
                    case 'cc_rejected_insufficient_amount':
                        $response_error['field_id'] = 'cardNumber';
                        $response_error['message'] = 'O cartão possui saldo insuficiente';
                        break;
                    case 'cc_rejected_invalid_installments':
                        $response_error['field_id'] = 'installments';
                        $response_error['message'] = $payment->payment_method_id . ' não processa pagamentos em ' . $payment->installments . ' parcelas.';
                        break;
                    case 'cc_rejected_max_attempts':
                        $response_error['field_id'] = '';
                        $response_error['message'] = 'Você atingiu o limite de tentativas permitido. Escolha outro cartão ou outra forma de pagamento.';
                        break;
                    case 'cc_rejected_other_reason':
                        $response_error['field_id'] = '';
                        $response_error['message'] = $payment->payment_method_id . ' não processa o pagamento.';
                        break;
                    case 'cc_rejected_card_type_not_allowed':
                        $response_error['field_id'] = 'cardNumber';
                        $response_error['message'] = 'O pagamento foi rejeitado porque o usuário não tem a função crédito habilitada em seu cartão multiplo (débito e crédito).';
                        break;
                    default:
                        break;
                }
                $response = new JsonResponse($response_error, 400);
            }
            return $response;
        } catch (\Exception $exception) {
            $exArr = json_decode($exception->getMessage(), true); // can be pushed to MercadoPago\RecuperableError
            dump($exArr);
            $response_error = [
                'field_id' => '',
                'message' => $exArr['error']['causes'][0]['description'],
            ];

            return new JsonResponse($response_error, 400);
        }
    }

    public function validate_payment_result($payment)
    {
        if ($payment->id === null) {
            $error_message = 'Unknown error cause';
            $error_code = -1;

            if ($payment->error !== null) {
                $sdk_error_message = $payment->error->message;
                if ($sdk_error_message == null) {
                    // $error_code = $payment->error->error;
                    $error_message = print_r($payment->error, true);
                } else {
                    $errorArray = $payment->toArray(['error' => ['causes']]);
                    $error_message = json_encode($errorArray);
                }
            }

            throw new \Exception($error_message, $error_code);
        }
    }

    /**
     * Undocumented function
     * @Route("/pix", name="agendamento_pagamento_payment_mercadopago_pix", methods={"GET", "POST"})
     * @param Request $request
     * @param Agendamento $agendamento
     * @param UrlGeneratorInterface $urlGeneratorInterface
     * @return Response
     */
    public function payByPixMercadoPago(Request $request, Agendamento $agendamento, UrlGeneratorInterface $urlGeneratorInterface): Response
    {
        $form = $this->createForm(AgendamentoPagamentoPixType::class, null);
        $form->handleRequest($request);
        return $this->render('agendamento_pagamento/pix.html.twig', [
            'form' => $form->createView(),
            'gen_pix_path' => $urlGeneratorInterface->generate('agendamento_pagamento_payment_mercadopago_pix_generate', ['id' => $agendamento->getId()]),
            'cc_pay_path' => $urlGeneratorInterface->generate('agendamento_pagamento_payment_mercadopago_card', ['id' => $agendamento->getId()]),
        ]);
    }

    /**
     * @Route("/pix/generate", name="agendamento_pagamento_payment_mercadopago_pix_generate", methods={"POST"})
     */
    public function generatePixMercadoPago(Request $request, Agendamento $agendamento, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        $apPix = $serializer->deserialize($request->getContent(), AgendamentoPagamentoPix::class, 'json');
        $errors = $validator->validate($apPix);
        if (count($errors) > 0) {
            return new JsonResponse($errors, 400);
        }

        $nome = strpos($apPix->getName(), ' ');
        MercadoPago\SDK::setAccessToken($_ENV['MERCADOPAGO_SECRET']);

        $payment = new MercadoPago\Payment();
        $payment->transaction_amount = $this->calculaValorTotalAgendamento($agendamento);
        $payment->description = 'Pagamento de serviços via Iroko';
        $payment->payment_method_id = "pix";
        $payment->payer = array(
            "email" => $apPix->getEmail(),
            "first_name" => substr($apPix->getName(), 0, $nome - 1),
            "last_name" => substr($apPix->getName(), $nome + 1),
            "identification" => array(
                "type" => $apPix->getIdentificationType(),
                "number" => $apPix->getIdentificationNumber(),
            ),
        );

        $payment->save();
        if ($payment->error != null) {
            return new JsonResponse($payment->error, 400);
        }

        $response_fields = [
            'id' => $payment->id,
            'status' => $payment->status,
            'detail' => $payment->status_detail,
            'codes' => [
                'qr_code_base64' => $payment->point_of_interaction->qr_code_base64,
                'qr_code' => $payment->point_of_interaction->qr_code,
            ],
        ];

        $reg = (new AgendamentoPagamento())
            ->setAgendamento($agendamento)
            ->setCapturado(true)
            ->setFormaPagto('PIX')
            ->setProcessador('MERPAGO')
            ->setStatusAtual($payment->status)
            ->setValor($payment->transaction_amount)
            ->setLog($response_fields)
        ;

        $agendamento->setConcluido(true);
        $em = $this->doctrine->getManager();
        $em->persist($reg);
        $em->flush();

        return new JsonResponse($payment->point_of_interaction->transaction_data);
    }

    private function calculaValorTotalAgendamento(Agendamento $agendamento): string
    {
        $valorTotal = 0.0;
        $servicos = $agendamento->getServicos();
        foreach ($servicos as $row) {
            $valorTotal += $row->getServico()->getValor();
        }
        return (string) $valorTotal;
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
