<?php

namespace App\Controller;

use App\Entity\Empresa;
use App\Entity\FuncionarioTurnoTrabalho;
use App\Entity\PerfilFuncionario;
use App\Form\FuncionarioTurnoTrabalhoType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/funcionario/{nomeUsuario}/escala")
 * @IsGranted("ROLE_ADMIN")
 */
class FuncionarioTurnoTrabalhoController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $mr)
    {
        $this->doctrine = $mr;
    }

    /**
     * @Route("/", name="escala_trabalho_index", methods={"GET"})
     */
    public function index(string $nomeUsuario): Response
    {
        $escalaTrabalhos = $this->doctrine
            ->getRepository(FuncionarioTurnoTrabalho::class)
            ->findBy(['cpfFuncionario' => $nomeUsuario])
        ;
        $funcionario = $this->doctrine
            ->getRepository(PerfilFuncionario::class)
            ->find($nomeUsuario)
        ;

        return $this->render('escala_trabalho/index.html.twig', [
            'escala_trabalhos' => $escalaTrabalhos,
            'funcionario' => $funcionario,
        ]);
    }

    /**
     * @Route("/new", name="escala_trabalho_new", methods={"GET","POST"})
     */
    public function new (Request $request, PerfilFuncionario $funcionario, UrlGeneratorInterface $urlGen): Response {
        $escalaTrabalho = new FuncionarioTurnoTrabalho();
        $form = $this->createForm(FuncionarioTurnoTrabalhoType::class, $escalaTrabalho, ['operacao' => 'novo']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $escalaTrabalho
                ->setCnpj($this->doctrine->getRepository(Empresa::class)->find($request->cookies->get('cnpj')))
                ->setCpfFuncionario($funcionario)
            ;
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($escalaTrabalho);
            $entityManager->flush();
            $this->addFlash('sucesso', 'Informações salvas!');
            return $this->redirectToRoute('escala_trabalho_index', ['nomeUsuario' => $funcionario->getNomeUsuario()]);
        }

        return $this->render('escala_trabalho/new.html.twig', [
            'escala_trabalho' => $escalaTrabalho,
            'form' => $form->createView(),
            'funcionario' => $funcionario,
            'path_cancelar' => $urlGen->generate('escala_trabalho_index', ['nomeUsuario' => $funcionario->getNomeUsuario()]),
        ]);
    }

    public function show(FuncionarioTurnoTrabalho $escalaTrabalho): Response
    {
        return $this->render('escala_trabalho/show.html.twig', [
            'escala_trabalho' => $escalaTrabalho,
        ]);
    }

    /**
     * @Route("/{cnpj}/{diaSemana}/edit", name="escala_trabalho_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, string $nomeUsuario, string $cnpj, int $diaSemana, UrlGeneratorInterface $urlGen): Response
    {
        $doctrine = $this->doctrine;
        $escalaTrabalho = $doctrine->getRepository(FuncionarioTurnoTrabalho::class)->findOneBy([
            'cpfFuncionario' => $nomeUsuario,
            'cnpj' => $cnpj,
            'diaSemana' => $diaSemana,
        ]);
        if ($escalaTrabalho === null) {
            return new Response('', 404);
        }
        $form = $this->createForm(FuncionarioTurnoTrabalhoType::class, $escalaTrabalho);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $novaEscalaTrabalho = (new FuncionarioTurnoTrabalho())
                ->setCnpj($escalaTrabalho->getCnpj())
                ->setCpfFuncionario($escalaTrabalho->getCpfFuncionario())
                ->setDiaSemana($form->get('diaSemana')->getViewData())
                ->setHoraInicio($form->get('horaInicio')->getNormData())
                ->setHoraFim($form->get('horaFim')->getNormData())
            ;
            $manager = $doctrine->getManager();
            $manager->persist($novaEscalaTrabalho);
            $manager->remove($escalaTrabalho);
            $doctrine->getManager()->flush();
            $this->addFlash('sucesso', 'Informações salvas!');
            return $this->redirectToRoute('escala_trabalho_index', ['nomeUsuario' => $nomeUsuario, 'cnpj' => $cnpj]);
        }

        return $this->render('escala_trabalho/edit.html.twig', [
            'escala_trabalho' => $escalaTrabalho,
            'form' => $form->createView(),
            'path_cancelar' => $urlGen->generate('escala_trabalho_index', ['nomeUsuario' => $nomeUsuario, 'cnpj' => $cnpj]),
            'include_delete' => true,
        ]);
    }

    /**
     * @Route("/{cnpj}/{diaSemana}", name="escala_trabalho_delete", methods={"DELETE"})
     */
    public function delete(Request $request, string $nomeUsuario, string $cnpj, int $diaSemana): Response
    {
        $doctrine = $this->doctrine;
        $escalaTrabalho = $doctrine->getRepository(FuncionarioTurnoTrabalho::class)->findOneBy([
            'cpfFuncionario' => $nomeUsuario,
            'cnpj' => $cnpj,
            'diaSemana' => $diaSemana,
        ]);
        if ($escalaTrabalho === null) {
            return new Response('', 404);
        }
        if ($this->isCsrfTokenValid('delete' . $escalaTrabalho->getDiaSemana(), $request->request->get('_token'))) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($escalaTrabalho);
            $entityManager->flush();
            $this->addFlash('sucesso', 'Informações apagadas!');
        }

        return $this->redirectToRoute('escala_trabalho_index');
    }

}
