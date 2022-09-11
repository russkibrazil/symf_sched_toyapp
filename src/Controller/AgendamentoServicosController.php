<?php

namespace App\Controller;

use DateTime;
use app\Entity\Servico;
use App\Entity\AgendamentoServicos;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/agendamentos/{agendamento}/servicos")
 * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
 */
class AgendamentoServicosController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $mr)
    {
        $this->doctrine = $mr;
    }

    /**
     * @Route("/", name="agendamento_servicos_index", methods={"GET"})
     */
    public function index($agendamento): Response
    {
        $agendamentoServicos = $this->doctrine
            ->getRepository(AgendamentoServicos::class)
            ->findBy(['agendamento' => $agendamento]);

        return $this->render('agendamento_servicos/index.html.twig', [
            'agendamento_servicos' => $agendamentoServicos,
        ]);
    }

    /**
     * @Route("/new", name="agendamento_servicos_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $agendamentoServico = new AgendamentoServicos();
        $servicos = $this->doctrine->getRepository(Servico::class)->findBy(['empresa' => $request->cookies->get('cnpj')]);

        $form = $this->createFormBuilder($agendamentoServico)
            ->add('agendamento')
            ->add('servico', ChoiceType::class, [
                'choices' => $servicos,
                'choice_value' => 'id',
                'choice_label' => function (?Servico $s){
                    return $s ? $s->getServico() : '';
                }
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($agendamentoServico);
            $entityManager->flush();
            $this->addFlash('sucesso', 'Informações salvas!');
            return $this->redirectToRoute('agendamento_servicos_index');
        }

        return $this->render('agendamento_servicos/new.html.twig', [
            'agendamento_servico' => $agendamentoServico,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{servico}", name="agendamento_servicos_delete", methods={"DELETE"})
     */
    public function delete(Request $request, AgendamentoServicos $agendamentoServico): Response
    {
        if ((time() - $agendamentoServico->getAgendamento()->getHorario(true)->getTimestamp()) <= 0)
        {
            $this->addFlash('erro', 'Não é possível excluir um evento do passado.');
        }
        else
        {
            if ($this->isCsrfTokenValid('delete'.$agendamentoServico->getAgendamento(), $request->request->get('_token'))) {
                $entityManager = $this->doctrine->getManager();
                $entityManager->remove($agendamentoServico);
                $entityManager->flush();
                $this->addFlash('sucesso', 'Informações apagadas!');
            }
        }

        return $this->redirectToRoute('agendamento_servicos_index');
    }
}
