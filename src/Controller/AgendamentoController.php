<?php

namespace App\Controller;

use App\Entity\Agendamento;
use App\Entity\AgendamentoCancelamento;
use App\Entity\Empresa;
use App\Entity\EmpresaTurnoTrabalho;
use App\Entity\FuncionarioLocalTrabalho;
use App\Entity\PerfilCliente;
use App\Entity\Pessoa;
use App\Entity\Servico;
use App\Form\AgendamentoType;
use DateInterval;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/agendamentos")
 * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
 *
 */
class AgendamentoController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $mr)
    {
        $this->doctrine = $mr;
    }
    /**
     * @Route("/", name="agendamentos_index", methods={"GET"})
     * @todo criar switcher para adiministradores verem sua agenda ou a agenda do recinto
     * @todo Alternar entre correntes e futuros (agenda recinto)
     */
    public function index(Request $request): Response
    {
        /**
         * @var \App\Repository\AgendamentoRepository $agRepo
         */
        $agRepo = $this->doctrine->getRepository(Agendamento::class);
        if ($this->isGranted("ROLE_RECEPCAO")) {
            $agendamentos = $agRepo->findByGreaterThanHorario([
                'datahora' => new DateTime(),
                'empresa' => $request->cookies->get('cnpj'),
            ]);
        } else {
            $role = $this->isGranted("ROLE_PRESTADOR") ? "ROLE_PRESTADOR" : "ROLE_USER";
            $agendamentos = $agRepo->findByGreaterThanHorario([
                'datahora' => new DateTime(),
                'empresa' => $request->cookies->get('cnpj'),
                'usuario' => $this->getUser(),
                'roleUsuario' => $role,
            ]);
        }

        return $this->render('agendamentos/index.html.twig', [
            'agendamentos' => $agendamentos,
            'nomeUsuario' => $this->getUser()->getPessoa()->getNome(),
            'titulo' => 'Agendamentos futuros',
            'reasonsArray' => AgendamentoCancelamento::defaultCancellingReasons()
        ]);
    }
    /**
     * @Route("/index/chega/{id}", name="agendamentos_index_chegada", methods={"POST"})
     */
    public function clienteChegou($id): Response
    {
        $r = $this->doctrine->getRepository(Agendamento::class)->find($id);
        if ($r == null) {
            return $this->json('Agendamento Inexistente', 404);
        }
        $r->setCompareceu(true);
        $this->doctrine->getManager()->flush();
        return $this->json('Atualizado');
    }

    /**
     * Adiciona 10 minutos no horário do agendamento, sob pena de adicionar um strike na ficha do cliente
     *
     * @Route("/index/atraso/{id}", name="agendamentos_index_atraso", methods={"POST"})
     * @param int $id
     * @return JsonResponse
     */
    public function clienteAtrasou($id): Response
    {
        /**
         * @var \App\Entity\Agendamento|null $r
         */
        $r = $this->doctrine->getRepository(Agendamento::class)->find($id);
        if ($r == null) {
            return $this->json('Agendamento Inexistente', 404);
        }
        $r->setAtrasado(true);

        $r->setHorario($r->getHorario(true)->add(new DateInterval('PT10M'))->format('Y-m-d H:i:s'));

        $empresa = $r->getEmpresa();
        /**
         * @var \App\Entity\ClienteAvaliacao[] $repSheet
         */
        $repSheet = $r->getCliente()->getUsuarioReputacao()->toArray();

        $perfil = array_filter($repSheet,
            function ($el) use ($empresa) {
                return $el->getCnpj() === $empresa;
            })
        ;
        $perfil[0]->novoAtraso();
        $this->doctrine->getManager()->flush();
        return $this->json('Atualizado');
    }

    /**
     * @Route("/index/cancela/{id}", name="agendamentos_index_cancela", methods={"POST"})
     * @param int $id
     * @return JsonResponse
     */
    public function clienteCancelou($id, Request $request, MailerInterface $mailer): Response
    {
        $r = $this->doctrine->getRepository(Agendamento::class)->find($id);
        if ($r == null) {
            return $this->json('Agendamento Inexistente', 404);
        }
        if (null != $r->getCancelado() || $r->getConcluido())
        {
            return new JsonResponse(['message' => 'The event can not be cancelled', 400]);
        }

        $cancelInfo = [];
        try {
            $cancelInfo = $request->toArray();
        } catch (\Throwable $th) {
            return new JsonResponse(['message' => 'Empty body'], 400);
        }

        if (!(array_key_exists('reason', $cancelInfo) && array_key_exists('reason_description', $cancelInfo)))
        {
            return new JsonResponse(['message' => 'Insuffient body data'], 400);
        }

        $r->setCancelado(new DateTime());

        $empresa = $r->getEmpresa();
        /**
         * @var \App\Entity\ClienteAvaliacao $repSheet
         */
        $repSheet = $r
            ->getCliente()
            ->getUsuarioReputacao()
            ->filter(function ($el) use ($empresa) {
                return $el->getCnpj() == $empresa;
            })
            ->first()
        ;
        $repSheet->novoCancelamento();

        $reasonText = $cancelInfo['reason'];
        if (strlen($cancelInfo['reason_description']) > 0)
        {
            $reasonText .= (' - ' . $cancelInfo['reason_description']);
        }
        $cancelOwner = $this->getUser();
        $cancelRecord = (new AgendamentoCancelamento())
            ->setAgendamento($r)
            ->setCancelledTs(new DateTime())
            ->setRequestedBy($cancelOwner)
            ->setReason($reasonText)
        ;

        $manager = $this->doctrine->getManager();
        $manager->persist($cancelRecord);
        $manager->flush();

        $whoCanceled = 'pela empresa';
        if ($cancelOwner == $r->getCliente()) {
            $whoCanceled = 'pelo cliente';
        } else if ($cancelOwner == $r->getFuncionario()) {
            $whoCanceled = 'pelo prestador';
        }

        $scheduleDate = $r->getHorario();

        $mail = (new TemplatedEmail())
            ->from('no-reply@oddboxmedia.com.br')
            ->bcc($r->getCliente()->getEmail(), $r->getFuncionario()->getEmail())
            ->subject('Um agendamento foi cancelado')
            ->htmlTemplate('agendamentos/cancel_mail.html.twig')
            ->context([
                'schedule' => $scheduleDate,
                'whoCanceled' => $whoCanceled,
                'cancelReason' => $reasonText
            ])
        ;
        $mailer->send($mail);

        return new JsonResponse(['message' => 'Event Updated']);
    }

    /**
     * @Route("/new", name="agendamentos_new", methods={"GET","POST"})
     */
    function new (Request $request): Response {
        /**
         * @var \App\Entity\Empresa $empresa
         */
        $empresa = $this->doctrine->getRepository(Empresa::class)->find($request->cookies->get('cnpj'));
        $agendamento = (new Agendamento())
            ->setEmpresa($empresa);
        $form = $this->createForm(AgendamentoType::class, $agendamento, ['empresa' => $empresa->getCnpj(), 'operacao' => 'NOVO']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $agendamento->setCliente($this->doctrine->getRepository(PerfilCliente::class)->find($form->get('cpf')->getViewData()));
            // workaround para evitar modificação do horário inicial do agendamento
            $temp = $agendamento->getHorario(true)->format('c');
            $agendamento->setConclusaoEsperada($this->calculaConclusao($agendamento)->format('c'));
            $temp = $agendamento->setHorario($temp);
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($agendamento);
            $entityManager->flush();
            $this->flashSucesso();
            return $this->redirectToRoute('agendamentos_index');
        }
        return $this->render('agendamentos/new.html.twig', [
            'form' => $form->createView(),
            'c' => 'valor',
            'id' => '',
        ]);
    }

    /**
     * @Route("/new/buscar-cliente/{c}", name="agendamentos_busca_nome", methods={"GET"})
     *
     * @return Response
     */
    public function obterClientesPesquisados($c): JsonResponse
    {
        /**
         * @var \App\Repository\PessoaRepository $uRepo
         */
        $uRepo = $this->doctrine->getRepository(Pessoa::class);
        $res = $uRepo->findByNome($c);
        if ($res != []) {
            return new JsonResponse($res);
        }
        return new JsonResponse(null, 404);
    }

    /**
     * @Route("/novoi", name="agendamentos_new_interactive", methods={"GET", "POST"})
     *
     */
    public function newi(Request $request): Response
    {
        /**
         * @var \App\Entity\Empresa $empresa
         */
        $empresa = $this->doctrine->getRepository(Empresa::class)->find($request->cookies->get('cnpj'));

        $agendamento = (new Agendamento())
            ->setEmpresa($empresa);

        $funcionarios = $this->carregarFuncionarios($empresa->getCnpj());
        $servicos = $this->doctrine->getRepository(Servico::class)->findBy(['empresa' => $empresa->getCnpj()]);
        $form = $this->createForm(AgendamentoType::class, $agendamento, ['empresa' => $empresa->getCnpj(), 'operacao' => 'NOVO']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->isGranted('ROLE_USER')) {
                $agendamento->setCliente($this->getUser());
            } else {
                $agendamento->setCliente($this->doctrine->getRepository(PerfilCliente::class)->find($form->get('cpf')->getViewData()));
            }

            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($agendamento);
            $entityManager->flush();
            $this->flashSucesso();
            return $this->redirectToRoute('agendamentos_index');
        }

        return $this->render('agendamentos/new_interactive.html.twig', [
            'funcionarios' => $funcionarios,
            'servicos' => $servicos,
            'form' => $form->createView(),
            'c' => 'valor',
            'id' => '',
        ]);
    }

    /**
     * Função utilizada para carregar a agenda do funcionário na data selecionda do agendamento interativo
     *
     * @param int $f
     * @param \Date $d
     * @return JsonResponse
     *
     * @Route("/novoi/agendamentos-funcionario/{f}/{d}", name="interativo_agendamentos_funcionario", methods={"GET"})
     */
    public function obterAgendamentoFuncionario($f, $d): JsonResponse
    {
        /**
         * @var \App\Repository\AgendamentoRepository $agRepo
         */
        $agRepo = $this->doctrine->getRepository(Agendamento::class);
        $res = $agRepo->findByBetweenHorarioDia($d, $f);
        if ($res !== null) {
            $r = [];
            foreach ($res as $value) {
                $r[] = [
                    'inicio' => $value->getHorario(true),
                    'fim' => $value->getConclusaoEsperada(),
                ];
            }
            return $this->json($r);
        }
        return $this->json(['agendamentos' => 'NA']);
    }

    /**
     * Função para retornar horario de funcionamento via AJAX. Não prevê feriados.
     *
     * @param string $ds
     * @return Response
     *
     * @Route("/novoi/horario-f/{ds}", name="interativo_horario_empresa", methods={"GET"})
     */
    public function obterHorarioFuncionamento(Request $request, $ds): Response
    {
        // $ds = date('N', strtotime($ds));
        /** @var \App\Entity\EmpresaTurnoTrabalho[] $res */
        $res = $this->doctrine->getRepository(EmpresaTurnoTrabalho::class)
            ->findBy([
                'empresa' => $request->cookies->get('cnpj'),
                // 'diaSemana' => $ds,
            ]);
        if ($res !== []) {
            $rJson = [];
            foreach ($res as $row) {
                $rJson[] = [
                    'diaSemana' => $row->getDiaSemana(),
                    'horaInicio' => $row->getHoraInicio(),
                    'horaFim' => $row->getHoraFim(),
                    'tsInicio' => $row->getHoraInicio()->getTimestamp(),
                    'tsFim' => $row->getHoraFim()->getTimestamp(),
                ];
            }
            return $this->json($rJson);
        } else {
            return new JsonResponse(null, 404);
        }

    }
    /**
     * @Route("/{id}", name="agendamentos_show", requirements={"id": "\d+"}, methods={"GET"})
     */
    public function show(Agendamento $agendamento): Response
    {
        return $this->render('agendamentos/show.html.twig', [
            'agendamento' => $agendamento,
            'reasonsArray' => AgendamentoCancelamento::defaultCancellingReasons()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="agendamentos_edit", requirements={"id": "\d+"}, methods={"GET","POST"})
     */
    public function edit(Request $request, Agendamento $agendamento): Response
    {
        $form = $this->createForm(AgendamentoType::class, $agendamento, ['empresa' => $request->cookies->get('cnpj')]);
        $form->handleRequest($request);
        $cliente = $agendamento->getCliente();

        if ($form->isSubmitted() && $form->isValid()) {
            $agendamento->setCliente($cliente);
            $this->doctrine->getManager()->flush();
            $this->flashSucesso();
            return $this->redirectToRoute('agendamentos_index');
        }

        return $this->render('agendamentos/edit.html.twig', [
            'agendamento' => $agendamento,
            'form' => $form->createView(),
            'c' => $cliente->getPessoa()->getNome(),
            'id' => $cliente->getNomeUsuario(),
            'include_delete' => true,
        ]);
    }

    /**
     * @Route("/{id}", name="agendamentos_delete", requirements={"id": "\d+"}, methods={"DELETE"})
     */
    public function delete(Request $request, Agendamento $agendamento): Response
    {
        if ($this->isCsrfTokenValid('delete' . $agendamento->getId(), $request->request->get('_token'))) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($agendamento);
            $entityManager->flush();
            $this->addFlash('sucesso', 'Agendamento apagado!');
        }

        return $this->redirectToRoute('agendamentos_index');
    }

    private function carregarFuncionarios(int $empresaAtiva): array
    {
        /** @var \App\Repository\FuncionarioLocalTrabalhoRepository $fltR */
        $fltR = $this->doctrine->getRepository(FuncionarioLocalTrabalho::class);
        $lt = $fltR->findFuncionarioByPrivilegio('prestador', $empresaAtiva);

        $funcionarios = [];
        foreach ($lt as $r) {
            $funcionarios[] = $r->getcpfFuncionario();
        }
        return $funcionarios;
    }

    private function flashSucesso()
    {
        $this->addFlash('sucesso', 'Agendamento salvo!');
    }

    private function calculaConclusao(Agendamento $agendamento): DateTime
    {
        $estados_horario = [
            0 => 'H',
            1 => 'M',
            2 => 'S',
        ];
        $horaC = $agendamento->getHorario(true);
        if ($horaC == null) {
            throw new Exception('Agendamento sem horário');
        }

        /** @var \App\Entity\AgendamentoServicos[] $res */
        $res = $agendamento->getServicos()->toArray();
        if (count($res) == 0) {
            throw new Exception('Nenhum serviço para calcular');
        }

        foreach ($res as $row) {
            $dur = $row->getServico()->getDuracao();
            $ts = "";
            for ($i = 0; $i < 3; $i++) {
                $temp = (int) substr($dur, 2 * $i, 2);
                if ($temp > 0) {
                    $ts .= ((string) $temp . $estados_horario[$i]);
                }
            }
            $horaC->add(new DateInterval('PT' . $ts));
        }
        return $horaC;
    }

    /**
     * Marcar agendamento como concluído
     * @Route("/index/conclui/{id}", name="agendamentos_index_concluir", methods={"POST"})
     * @param Request $request
     * @param Agendamento $agendamento
     * @return JsonResponse
     */
    public function concluirAgendamentoAjax(Request $request, Agendamento $agendamento): JsonResponse
    {
        $agendamento->setConcluido(true);
        $this->doctrine->getManager()->flush();
        return new JsonResponse();
    }

    /**
     * Rota para visualizar agendmaentos com pagamento não processado/realizado
     * @Route("/pendente", name="agendamentos_pendentes", methods={"GET"})
     * @return Response
     */
    public function historicoAgendamentosPendentes(): Response
    {
        /**
         * @var \App\Repository\AgendamentoRepository $aRepo
         */
        $aRepo = $this->doctrine->getRepository(Agendamento::class);
        $pagamentosPendentes = $aRepo->getExecutados(false);
        return $this->render('agendamentos/index.html.twig', [
            'agendamentos' => $pagamentosPendentes,
            'nomeUsuario' => $this->getUser()->getPessoa()->getNome(),
            'titulo' => 'Agendamentos pendentes',
            'reasonsArray' => array('')
        ]);
    }

    /**
     * Rota para visualizar agendmaentos com pagamento processado/realizado
     * @Route("/pago", name="agendamentos_pagos", methods={"GET"})
     * @return Response
     */
    public function historicoAgendamentosPagos(): Response
    {
        /**
         * @var \App\Repository\AgendamentoRepository $aRepo
         */
        $aRepo = $this->doctrine->getRepository(Agendamento::class);
        $pagamentosPendentes = $aRepo->getExecutados(true);
        return $this->render('agendamentos/index.html.twig', [
            'agendamentos' => $pagamentosPendentes,
            'nomeUsuario' => $this->getUser()->getPessoa()->getNome(),
            'titulo' => 'Agendamentos encerrados',
            'reasonsArray' => array('')
        ]);
    }
}
