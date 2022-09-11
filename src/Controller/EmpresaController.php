<?php

namespace App\Controller;

use App\Entity\Empresa;
use App\Form\EmpresaType;
use App\Form\EmpresaInfoBasicaType;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\EmpresaPoliticasBloqueioType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/empresa")
 * @IsGranted("ROLE_ADMIN")
 */
class EmpresaController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $mr)
    {
        $this->doctrine = $mr;
    }

    /**
     * @Route("/", name="configuracao_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $eRepo = $this->doctrine->getRepository(Empresa::class);
        if ($this->isGranted('ROLE_PROPRIETARIO'))
        {
            $configuracao = $eRepo->findAll();
        }
        else
        {
            $configuracao = $eRepo->findBy(['cnpj' => $request->cookies->get('cnpj')]);
        }

        return $this->render('configuracao/index.html.twig',[
            'configuracaos' => $configuracao
        ]);
    }

    /**
     * @Route("/{cnpj}", name="configuracao_show", methods={"GET"})
     */
    public function show(Empresa $configuracao): Response
    {
        return $this->render('configuracao/show.html.twig', [
            'configuracao' => $configuracao,
        ]);
    }

    /**
     * @Route("/{cnpj}/edit", name="configuracao_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Empresa $configuracao, UrlGeneratorInterface $urlGen): Response
    {
        $form = $this->createForm(EmpresaType::class, $configuracao, ['operacao' => EmpresaType::EDITAR]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $h = $configuracao->getHorarioTrabalho();
            foreach($h as $dia){
                $configuracao->removeHorarioTrabalho($dia);
                $dia->setEmpresa($configuracao);
                $configuracao->addHorarioTrabalho($dia);
                $this->doctrine->getManager()->persist($dia);
            }
            $this->doctrine->getManager()->flush();
            $this->addFlash('sucesso', 'Informações salvas!');
            return $this->redirectToRoute('configuracao_show', ['cnpj' => $configuracao->getCnpj()]);
        }

        return $this->render('configuracao/edit.html.twig', [
            'configuracao' => $configuracao,
            'form' => $form->createView(),
            'path_cancelar' => $urlGen->generate('configuracao_show', ['cnpj' => $configuracao->getCnpj()])
        ]);
    }

    /**
     * @Route("/{cnpj}/edit_bloqueio", name="configuracao_edit_bloqueio", methods={"GET","POST"})
     */
    public function editBloqueio(Request $request, Empresa $configuracao, FormFactoryInterface $formFactory) : Response
    {
        $form = $formFactory->createNamed('empresa', EmpresaPoliticasBloqueioType::class, $configuracao);
        // $form = $this->createForm(EmpresaPoliticasBloqueioType::class, $configuracao);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->doctrine->getManager()->flush();
            $this->addFlash('sucesso', 'Informações salvas!');
            return $this->redirectToRoute('configuracao_show', [
                'cnpj' => $configuracao->getCnpj()
            ]);
        }
        return $this->render('configuracao/edit_politicaBloqueio.html.twig',[
            'configuracao' => $configuracao,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{cnpj}/edit_basico", name="configuracao_edit_basico", methods={"GET","POST"})
     */
    public function editInfoBasica(Request $request, Empresa $configuracao, FormFactoryInterface $formFactory) : Response
    {
        // TODO Usar Formulário parcial de empresa, editado direto no template
        $form = $formFactory->createNamed('empresa', EmpresaInfoBasicaType::class, $configuracao);
        // $form = $this->createForm(EmpresaInfoBasicaType::class, $configuracao);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->doctrine->getManager()->flush();
            $this->addFlash('sucesso', 'Informações salvas!');
            return $this->redirectToRoute('configuracao_show', [
                'cnpj' => $configuracao->getCnpj()
            ]);
        }
        return $this->render('configuracao/edit_infoBasica.html.twig',[
            'configuracao' => $configuracao,
            'form' => $form->createView()
        ]);
    }
}
