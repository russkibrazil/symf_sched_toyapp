<?php

namespace App\Controller;

use App\Entity\Agendamento;
use App\Entity\AgendamentoPagamento;
use App\Entity\AgendamentoPagamentoRequest;
use App\Entity\Empresa;
use App\Form\AgendamentoPagamentoType;
use App\Repository\AgendamentoPagamentoRepository;
use DateInterval;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use JsonException;
use RandomLib;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @Route("/agendamentos/{id_agendamento}/pagamento")
 * @IsGranted("ROLE_CAIXA")
 */
class AgendamentoPagamentoController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $mr)
    {
        $this->doctrine = $mr;
    }

    /**
     * @Route("/", name="agendamento_pagamento_index", methods={"GET"})
     */
    public function index(AgendamentoPagamentoRepository $agendamentoPagamentoRepository, $id_agendamento): Response
    {
        return $this->render('agendamento_pagamento/index.html.twig', [
            'agendamento_pagamentos' => $agendamentoPagamentoRepository->findBy(['agendamento' => $id_agendamento]),
        ]);
    }

    /**
     * @Route("/new", name="agendamento_pagamento_new", methods={"GET","POST"})
     */
    public function new(Request $request, $id_agendamento): Response
    {
        $agendamentoPagamento = new AgendamentoPagamento();
        $form = $this->createForm(AgendamentoPagamentoType::class, $agendamentoPagamento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $agendamentoPagamento->setData(new DateTime());

            $agendamento = $this->doctrine->getRepository(Agendamento::class)->find($id_agendamento);
            $agendamentoPagamento->setAgendamento($agendamento);

            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($agendamentoPagamento);
            $entityManager->flush();

            return $this->redirectToRoute('agendamento_pagamento_index');
        }

        return $this->render('agendamento_pagamento/new.html.twig', [
            'agendamento_pagamento' => $agendamentoPagamento,
            'form' => $form->createView(),
        ]);
    }
    /**
     * Rota para pagamentos via Modal no index de agendamentos
     * @Route("/ajax", name="agendamento_pagamento_ajax", methods={"POST"})
     * @return Response
     */
    public function pagamentoViaAjax(Agendamento $id_agendamento, Request $request): Response
    {
        try
        {
            $arguments = $request->toArray();
        } catch (JsonException $e) {
            return new JsonResponse(['message' => 'Not enough arguments'], 422);
        }
        // TODO Refuse remote payments
        // TODO Enhance record filtering (chegou, cancelado...)
        if ($id_agendamento->getCompareceu()) {
            $pagamento = new AgendamentoPagamento();
            $pagamento->setAgendamento($id_agendamento);
            $pagamento->setData(new DateTime());
            $pagamento->setFormaPagto($arguments['fp']);
            $pagamento->setValor($arguments['valor']);
            $pagamento->setStatusAtual('PAGO');

            $id_agendamento->setConcluido(true);

            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($pagamento);
            $entityManager->flush();
            return $this->json(null);
        }
        return $this->json(null, 404);
    }

    /**
     * Rota para requerir pagamaentos à distância
     * @Route("/request", name="agendamento_pagamento_request", methods={"POST"})
     * @param Agendamento $id_agendamento
     * @param UrlGeneratorInterface $urlGen
     * @param MailerInterface $mailer
     * @return JsonResponse
     */
    public function sendPaymentRequest(Agendamento $id_agendamento, UrlGeneratorInterface $urlGen, MailerInterface $mailer): JsonResponse
    {
        $apr = $this->doctrine->getRepository(AgendamentoPagamentoRequest::class)->findOneBy(['agendamento' => $id_agendamento->getId()]);
        if ($apr instanceof AgendamentoPagamentoRequest) {
            // TODO: Refinar para os casos de tokens vencidos. Levar em consideração potencial flooding
            return new JsonResponse(['message' => 'Uma requisição já foi enviada. Aguarde o pagamento.'], 422);
        }
        $ap = $this->doctrine->getRepository(AgendamentoPagamento::class)->findBy(['agendamento' => $id_agendamento->getId()]);
        if ($ap !== []) {
            // TODO refinar para potenciais casos de estorno de valores
            return new JsonResponse(['message' => 'Agendamento já pago'], 422);
        }
        $gerador = (new RandomLib\Factory)->getLowStrengthGenerator();
        $token = $gerador->generateString(20, $gerador::CHAR_ALNUM);
        $urlTarget = $urlGen->generate('agendamento_pagamento_payment_method_select', ['id_agendamento' => $id_agendamento->getId(), 'prid' => $token]);
        $nomeEmpresa = $id_agendamento->getEmpresa()->getNomeEmpresa();
        $validade = (new DateTime())->add(new DateInterval('PT15M'));

        $servicos = $id_agendamento->getServicos();
        $totalServicos = 0.00;
        foreach ($servicos as $row) {
            $totalServicos += $row->getServico()->getValor();
        }

        $email = (new TemplatedEmail())
            ->from('no-reply@oddboxmedia.com.br')
            ->to($id_agendamento->getCliente()->getEmail())
            ->subject('Seu serviço em ' . $nomeEmpresa . ' foi concluído')
            ->htmlTemplate('agendamento_pagamento/mail_pagamento_request.html.twig')
            ->context([
                'agendamento' => $id_agendamento,
                'urlTarget' => $urlTarget,
                'nomeEmpresa' => $nomeEmpresa,
                'pix' => $id_agendamento->getEmpresa()->getEmpresaProcessadorPagamentos()->first()->getPix() == null ? false : true,
                'validade' => $validade,
                'valorServicos' => $totalServicos,
            ])
        ;
        $mailer->send($email);
        $regToken = (new AgendamentoPagamentoRequest())
            ->setAgendamento($id_agendamento)
            ->setToken($token)
            ->setValidade($validade);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($regToken);
        $entityManager->flush();
        return new JsonResponse(['id' => $id_agendamento->getId(), 'message' => 'Requisição de pagamento enviada']);
    }

    /**
     * @Route("/select", name="agendamento_pagamento_payment_method_select", methods={"GET"})
     */
    public function startPaymentClient(Request $request, Agendamento $id_agendamento): Response
    {
        $token = $request->query->get('prid');
        if ($token === null) {
            throw $this->createNotFoundException();
        }

        $aprRepo = $this->doctrine->getRepository(AgendamentoPagamentoRequest::class);
        $apr = $aprRepo->find($token);
        if ($apr == null) {
            throw $this->createNotFoundException();
        }

        $em = $this->doctrine->getManager(); // TODO confirm if the user is trying to recreate the request whenever request payment over an existent request
        $em->remove($apr); // FIXME Keep the token while the client does not pay it / delete the request after paid
        $em->flush(); // TODO Let the client access the request mannually through his/her schedule console
        if ($apr->getValidade()->getTimestamp() < time()) {
            throw $this->createAccessDeniedException('Expired Token');
        }
        $epps = $this->doctrine
            ->getRepository(Empresa::class)
            ->find($request->cookies->get('cnpj'))
            ->getEmpresaProcessadorPagamentos()
        ;
        if ($epps->count() > 1) {

        }
        $epp = $epps->first();
        if (($epp->getPix() ?? '') == '') {
            return $this->redirectToRoute('agendamento_pagamento_payment_mercadopago_card', ['id' => $apr->getAgendamento()->getId()]);
        }

        // TODO Mostrar todas as carteiras habilitadas
        // FIXME Somente redirecionar diretamente quando as carteiras não estiverem habilitadas e houver somente uma possibilidade de pagamento
        // TODO Se houver mais que um processador de pagamento, o sistema deve escolher o favorito indicado e ainda assim mostrar todas as carteiras disponíveis
        return $this->render('agendamento_pagamento/seletor_forma_pagamento.html.twig', [
            'epp' => $epp,
            'agendamento' => $id_agendamento,
        ]);
    }

    /**
     * @Route("/{id}", name="agendamento_pagamento_delete", methods={"DELETE"})
     */
    public function delete(Request $request, AgendamentoPagamento $agendamentoPagamento): Response
    {
        if ($this->isCsrfTokenValid('delete' . $agendamentoPagamento->getId(), $request->request->get('_token'))) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($agendamentoPagamento);
            $entityManager->flush();
        }

        return $this->redirectToRoute('agendamento_pagamento_index');
    }

    /**
     * Método para registro de reembolsos via JSON
     * @Route("/refund", name="agendamento_pagamento_reembolso_ajax", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function incluirReembolsoAjax(Request $request, Agendamento $id_agendamento, HttpClientInterface $httpClient): JsonResponse
    {
        $inputs = $request->toArray();
        // if (
        //     $this->isCsrfTokenValid('refund' . $id_agendamento->getId(), $inputs['_token']) ||
        //     $this->isCsrfTokenValid('refundmodal', $inputs['_token'])
        // )
        // {
            $valorRequisitado = abs(floatval($inputs['valor_reembolso']));
            if ($valorRequisitado == 0)
            {
                return new JsonResponse(['message' => 'Nenhum valor a reembolsar'], 400);
            }
            $pagamentos = $id_agendamento->getAgendamentoPagamentos();
            $pagamentosCandidatos = $pagamentos->filter(function ($el) {
                return $el->getStatusAtual() == 'approved';
            });
            if ($pagamentosCandidatos->count() == 0)
                return new JsonResponse(['message' => 'Não há pagamentos candidatos para reembolso'], 404);

            /**
             * @var \App\Entity\AgendamentoPagamento $pagamento
             */
            $pagamento = $pagamentosCandidatos->first();
            if ((date_add($pagamento->getData(), new DateInterval('6 months')))->getTimestamp() < time())
            {
                return new JsonResponse(['message' => 'O período permitido para reembolsos foi ultrapassado'], 400);
            }
            if ($valorRequisitado > $pagamento->getValor())
                $valorRequisitado = $pagamento->getValor();

            switch ($pagamento->getProcessador()) {
                case 'MERPAGO':
                    $idMP = $pagamento->getLog()[0]['id'];
                    $ppResponse = $httpClient->request(
                        'POST',
                        "https://api.mercadopago.com/v1/payments/$idMP/refunds",
                        [
                            'headers' => [
                                'Content-type' => 'application/json',
                                'Accept' => 'application/json',
                            ],
                            'auth-bearer' => $_ENV['MERCADOPAGO_SECRET'],
                            'body' => '{"amount":' . $valorRequisitado . '}',
                        ]
                    );
                    break;

                default:
                    return new JsonResponse(['message' => 'Payment processor not supported yet'], 501);
                    break;
            }
            if ($ppResponse->getStatusCode() == 200)
            {
                $streamContent = $ppResponse->toArray();
                if ($streamContent['status'] == 'approved')
                {
                    $log = $pagamento->getLog();
                    $log[] = $streamContent;
                    $pagamento
                        ->setLog($log)
                        ->setStatusAtual('refunded')
                        ->setUltimaModificacao(new \DateTime())
                    ;
                    $this->doctrine->getManager()->flush();
                    return new JsonResponse(['message' => 'Reembolso realizado. O valor estará disponível em breve para o cliente.'], 200);
                }
            }
            return new JsonResponse(['message' => 'Operação não aprovada'], 400);
        // }
        // return new JsonResponse(['message' => 'Token inválido'], 400);
    }

    /**
     * Método para registro de cancelamento de requisições de pagamento
     * @Route("/request/cancel", name="agendamento_pagamento_cancela_request_ajax", methods={"POST"})
     * @param Request $request
     * @param Agendamento $id_agendamento
     * @return JsonResponse
     */
    public function cancelaPedidoPagamentoAjax(Request $request, Agendamento $id_agendamento, HttpClientInterface $httpClient): JsonResponse
    {
        // $inputs = json_decode($request->getContent(), true);
        // if ($this->isCsrfTokenValid('cancelPaymentRequest' . $id_agendamento->getId(), $inputs['_token']))
        // {
            $pagamentos = $id_agendamento->getAgendamentoPagamentos();
            $pagamentosRestritos = $pagamentos->filter(function ($el) {
                return $el->getStatusAtual() == 'approved';
            });
            if ($pagamentosRestritos->count() > 0)
                return new JsonResponse(['message' => 'Operação não permitida'], 400);
            $pagamentosCandidatos = $pagamentos->filter(function ($el) {
                return in_array($el->getStatusAtual(), ['pending', 'in_process', 'authorized']);
            });
            if ($pagamentosCandidatos->count() == 0)
                return new JsonResponse(['message' => 'Não há pagamentos candidatos para estorno'], 404);
            /**
             * @var \App\Entity\AgendamentoPagamento $pagamento
             */
            $pagamento = $pagamentosCandidatos->first();
            switch ($pagamento->getProcessador()) {
                case 'MERPAGO':
                    $idMP = $pagamento->getLog()[0]['id'];
                    $ppResponse = $httpClient->request(
                        'PUT',
                        "https://api.mercadopago.com/v1/payments/$idMP",
                        [
                            'headers' => [
                                'Content-type' => 'application/json',
                                'Accept' => 'application/json',
                            ],
                            'auth-bearer' => $_ENV['MERCADOPAGO_SECRET'],
                        ]
                    );
                    break;

                default:
                    return new JsonResponse(['message' => 'Payment processor not supported yet'], 501);
                    break;
            }
            if ($ppResponse->getStatusCode() == 200)
            {
                $streamContent = $ppResponse->toArray();
                if ($streamContent['status'] == 'cancelled')
                {
                    $log = $pagamento->getLog();
                    $log[] = $streamContent;
                    $pagamento
                        ->setLog($log)
                        ->setStatusAtual('cancelled')
                        ->setUltimaModificacao(new \DateTime())
                    ;

                    $id_agendamento->setPagamentoPendente(true);
                    return new JsonResponse(['message' => 'Pagamento estornado. O agendamento está com o pagamento em aberto'], 200);
                }
            }
            return new JsonResponse(['message' => 'Operação não aprovada'], 400);


        // }
        // return new JsonResponse(['message' => 'Token inválido'], 400);
    }
}
