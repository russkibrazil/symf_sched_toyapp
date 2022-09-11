<?php

namespace App\Controller;

use App\Entity\Agendamento;
use App\Entity\EmpresaTurnoTrabalho;
use App\Form\EmpresaTurnoTrabalhoType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/configuracao/{cnpj}/horario")
 * @IsGranted("ROLE_ADMIN")
 */
class EmpresaTurnoTrabalhoController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $mr)
    {
        $this->doctrine = $mr;
    }

    /**
     * @Route("/", name="horario_trabalho_index", methods={"GET"})
     */
    public function index($cnpj): Response
    {
        $horarioTrabalhos = $this->doctrine
            ->getRepository(EmpresaTurnoTrabalho::class)
            ->findBy(['empresa' => $cnpj]);

        return $this->render('horario_trabalho/index.html.twig', [
            'horario_trabalhos' => $horarioTrabalhos,
            'cnpj' => $cnpj
        ]);
    }

    /**
     * @Route("/new", name="horario_trabalho_new", methods={"GET","POST"})
     */
    public function new($cnpj, Request $request): Response
    {
        $horarioTrabalho = new EmpresaTurnoTrabalho();
        $form = $this->createForm(EmpresaTurnoTrabalhoType::class, $horarioTrabalho);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($horarioTrabalho);
            $entityManager->flush();
            $this->addFlash('sucesso', 'Informações salvas!');
            return $this->redirectToRoute('horario_trabalho_index', ['cnpj' => $cnpj]);
        }

        return $this->render('horario_trabalho/new.html.twig', [
            'horario_trabalho' => $horarioTrabalho,
            'form' => $form->createView(),
            'cnpj' => $cnpj
        ]);
    }

    public function show(EmpresaTurnoTrabalho $horarioTrabalho): Response
    {
        // IDEA: Mostrar agendamentos futuros e funcionários trabalhando neste horário
        return $this->render('horario_trabalho/show.html.twig', [
            'horario_trabalho' => $horarioTrabalho,
        ]);
    }

    /**
     * @Route("/{diaSemana}/edit", name="horario_trabalho_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, EmpresaTurnoTrabalho $horarioTrabalho): Response
    {
        $form = $this->createForm(EmpresaTurnoTrabalhoType::class, $horarioTrabalho);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->flush();
            $this->addFlash('sucesso', 'Informações salvas! Os agendamentos prévios não foram alterados.');
            return $this->redirectToRoute('horario_trabalho_index', ['cnpj' => $horarioTrabalho->getEmpresa()->getCnpj()]);
        }

        return $this->render('horario_trabalho/edit.html.twig', [
            'horario_trabalho' => $horarioTrabalho,
            'form' => $form->createView(),
            'cnpj' => $horarioTrabalho->getEmpresa()->getCnpj()
        ]);
    }

    /**
     * @Route("/{diaSemana}", name="horario_trabalho_delete", methods={"DELETE", "POST"})
     */
    public function delete(Request $request, EmpresaTurnoTrabalho $horarioTrabalho): Response
    {
        if ($this->isCsrfTokenValid('delete' . $horarioTrabalho->getDiaSemana(), $request->request->get('_token'))) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($horarioTrabalho);
            $entityManager->flush();
            /**
             * @var App\Repository\AgendamentoRepository $aRepo
             */
            $aRepo = $entityManager->getRepository(Agendamento::class);
            $this->addFlash('sucesso', 'Informações apagadas!');
            if ($aRepo->findByDiaSemana(((int) $horarioTrabalho->getDiaSemana()) - 1) !== [])
            {
                switch ((int) $horarioTrabalho->getDiaSemana()) {
                    case 1:
                        $diaSemana = 'domingo';
                        break;
                    case 2:
                        $diaSemana = 'segunda';
                        break;
                    case 3:
                        $diaSemana = 'terça';
                        break;
                    case 4:
                        $diaSemana = 'quarta';
                        break;
                    case 5:
                        $diaSemana = 'quinta';
                        break;
                    case 6:
                        $diaSemana = 'sexta';
                        break;
                    case 7:
                        $diaSemana = 'sábado';
                        break;
                    default:
                        $diaSemana = 'feriado';
                        break;
                }
                $this->addFlash('erro', "Os agendamentos para $diaSemana não foram alterados.");
            }
        }

        return $this->redirectToRoute('horario_trabalho_index', ['cnpj' => $horarioTrabalho->getEmpresa()->getCnpj()]);
    }
}
