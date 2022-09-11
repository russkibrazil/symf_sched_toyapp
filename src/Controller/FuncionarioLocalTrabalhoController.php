<?php

namespace App\Controller;

use App\Entity\Empresa;
use App\Entity\FuncionarioLocalTrabalho;
use App\Entity\PerfilFuncionario;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\FuncionarioLocalTrabalhoType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/funcionario/{nomeUsuario}/locais")
 * @IsGranted("ROLE_ADMIN")
 */
class FuncionarioLocalTrabalhoController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $mr)
    {
        $this->doctrine = $mr;
    }

    public function index(): Response
    {
        $localTrabalhos = $this->doctrine
            ->getRepository(FuncionarioLocalTrabalho::class)
            ->findAll();

        return $this->render('local_trabalho/index.html.twig', [
            'local_trabalhos' => $localTrabalhos,
        ]);
    }

    /**
     * @Route("/new", name="local_trabalho_new", methods={"GET","POST"})
     */
    public function new(PerfilFuncionario $funcionario, Request $request, UrlGeneratorInterface $urlGen): Response
    {
        if ($funcionario->getFuncionarioLocalTrabalho() != null)
        {
            $this->addFlash('erro', 'Este perfil já tem um local de trabalho definido');
            return $this->redirectToRoute('funcionario_show', ['nomeUsuario' => $funcionario->getNomeUsuario()]);
        }
        $eRepo = $this->doctrine->getRepository(Empresa::class);
        $localTrabalho = (new FuncionarioLocalTrabalho())
            ->setCnpj($eRepo->find($request->cookies->get('cnpj')))
        ;
        $targetUserHasRole_Proprietario = array_search('ROLE_PROPRIETARIO', $funcionario->getRoles()) !== false ? true : false;
        $form = $this->createForm(
            FuncionarioLocalTrabalhoType::class,
            $localTrabalho,
            [
                'operacao' => 'novo',
                'currentUserHasRole_Proprietario' => $this->isGranted('ROLE_PROPRIETARIO'),
                'targetUserHasRole_Proprietario' => $targetUserHasRole_Proprietario,
            ]
        );
        if ($targetUserHasRole_Proprietario)
        {
            $form->get('privilegioAdmin')->setData(true);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roles = $this->transformaPrivilegiosParaEntidade($form);
            $localTrabalho->setPrivilegios($roles);
            $localTrabalho->setCpfFuncionario($funcionario);
            if (!$targetUserHasRole_Proprietario)
            {
                $funcionario->setRoles(array_map(function ($val) {return "ROLE_$val";}, $roles));
            }
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($localTrabalho);
            $entityManager->flush();
            $this->addFlash('sucesso', 'Informações salvas!');
            return $this->redirectToRoute('funcionario_show', ['nomeUsuario' => $funcionario->getNomeUsuario()]);
        }

        return $this->render('local_trabalho/new.html.twig', [
            'local_trabalho' => $localTrabalho,
            'form' => $form->createView(),
            'path_cancelar' => $urlGen->generate('funcionario_index')
        ]);
    }

    public function show(FuncionarioLocalTrabalho $localTrabalho): Response
    {
        return $this->render('local_trabalho/show.html.twig', [
            'local_trabalho' => $localTrabalho,
        ]);
    }

    /**
     * @Route("/{cnpj}/edit", name="local_trabalho_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, string $nomeUsuario, string $cnpj): Response
    {
        if ($cnpj != $request->cookies->get('cnpj'))
        {
            $this->denyAccessUnlessGranted('ROLE_PROPRIETARIO');
        }
        $localTrabalho = $this->doctrine->getRepository(FuncionarioLocalTrabalho::class)->find(['cnpj' => $cnpj, 'cpfFuncionario' => $nomeUsuario]);
        $targetUserHasRole_Proprietario = array_search('ROLE_PROPRIETARIO', $localTrabalho->getCpfFuncionario()->getRoles()) !== false ? true : false;
        $form = $this->createForm(FuncionarioLocalTrabalhoType::class, $localTrabalho, [
            'targetUserHasRole_Proprietario' => $targetUserHasRole_Proprietario,
        ]);

        $roles = ['Prestador', 'Caixa', 'Admin', 'Recepcao'];
        $privFunc = $localTrabalho->getPrivilegios();
        foreach ($roles as $role) {
            if (in_array(strtoupper($role), $privFunc)){
                $form->get('privilegio' . $role)->setData(true);
            }
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roles = $this->transformaPrivilegiosParaEntidade($form);
            $localTrabalho->setPrivilegios($roles);
            $funcionario = $localTrabalho->getCpfFuncionario();
            if (!$targetUserHasRole_Proprietario)
            {
                $funcionario->setRoles(array_map(function ($val) {return "ROLE_$val";}, $roles));
            }
            $this->doctrine->getManager()->flush();
            $this->addFlash('sucesso', 'Informações salvas!');
            return $this->redirectToRoute('funcionario_show', ['nomeUsuario' => $localTrabalho->getCpfFuncionario()->getNomeUsuario()]);
        }

        return $this->render('local_trabalho/edit.html.twig', [
            'local_trabalho' => $localTrabalho,
            'form' => $form->createView(),
            'include_delete' => true
        ]);
    }

    /**
     * @Route("/{cnpj}", name="local_trabalho_delete", methods={"DELETE"})
     */
    public function delete(Request $request, FuncionarioLocalTrabalho $workplace): Response
    {
        // FIXME Revogar privilégios
        if ($this->isCsrfTokenValid('delete'.$workplace->getCnpj()->getCnpj(), $request->request->get('_token'))) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($workplace);
            $entityManager->flush();
            $this->addFlash('sucesso', 'Informações apagadas!');
        }

        return $this->redirectToRoute('funcionario_show', ['nomeUsuario' => $workplace->getCpfFuncionario()->getNomeUsuario()]);
    }

    private function transformaPrivilegiosParaEntidade(\Symfony\Component\Form\FormInterface $form): array
    {
        $attr = ['privilegioPrestador', 'privilegioRecepcao', 'privilegioAdmin', 'privilegioCaixa'];
        $roles_res = [];
        foreach ($attr as $el) {
            if ($form->get($el)->getData() == '1')
            {
                $roles_res[] = strtoupper(substr($el,10));
            }
        }
        return $roles_res;
    }
}
