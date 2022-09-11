<?php

namespace App\Controller;

use App\Entity\Empresa;
use App\Entity\Servico;
use App\Form\ServicoType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/servicos")
 * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
 */
class ServicoController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $mr)
    {
        $this->doctrine = $mr;
    }

    /**
     * @Route("/", name="servicos_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $servicos = $this->doctrine
            ->getRepository(Servico::class)
            ->findBy(['empresa' => $request->cookies->get('cnpj')]);

        if ($this->isGranted('ROLE_ADMIN'))
        {
            return $this->render('servicos/index.html.twig', [
                'servicos' => $servicos,
            ]);
        }
        else
        {
            return $this->render('servicos/index_clientes.html.twig', [
                'servicos' => $servicos,
            ]);
        }
    }

    /**
     * @Route("/new", name="servicos_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request): Response
    {
        $servico = (new Servico())
            ->setEmpresa($this->doctrine->getRepository(Empresa::class)->find($request->cookies->get('cnpj')))
            ->setAtivo(true);
        $form = $this->createForm(ServicoType::class, $servico);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($servico);
            $entityManager->flush();
            $this->addFlash('sucesso', 'Informações salvas!');
            return $this->redirectToRoute('servicos_index');
        }

        return $this->render('servicos/new.html.twig', [
            'servico' => $servico,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="servicos_show", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(Servico $servico): Response
    {
        return $this->render('servicos/show.html.twig', [
            'servico' => $servico,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="servicos_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, Servico $servico): Response
    {
        $form = $this->createForm(ServicoType::class, $servico);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->doctrine->getManager()->flush();
            $this->addFlash('sucesso', 'Informações salvas!');
            return $this->redirectToRoute('servicos_index');
        }

        return $this->render('servicos/edit.html.twig', [
            'servico' => $servico,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="servicos_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Servico $servico): Response
    {
        if ($this->isCsrfTokenValid('delete'.$servico->getId(), $request->request->get('_token'))) {
            $servico->setAtivo(false);
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($servico);
            $entityManager->flush();
            $this->addFlash('sucesso', 'Informações apagadas!');
        }

        return $this->redirectToRoute('servicos_index');
    }
}
