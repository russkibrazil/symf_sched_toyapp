<?php

namespace App\Controller;

use App\Entity\Empresa;
use App\Entity\FuncionarioLocalTrabalho;
use App\Entity\PerfilFuncionario;
use App\Form\PerfilType;
use App\Service\LicensingHelper;
use Doctrine\Persistence\ManagerRegistry;
use RandomLib;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @Route("/funcionario")
 * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
 */
class FuncionarioController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $mr)
    {
        $this->doctrine = $mr;
    }

    /**
     * @Route("/", name="funcionario_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        /**
         * @var \App\Repository\FuncionarioLocalTrabalhoRepository $fltRepo
         */
        $fltRepo = $this->doctrine->getRepository(FuncionarioLocalTrabalho::class);
        $lt = $fltRepo->findByCnpj($request->cookies->get('cnpj'));
        if ($this->isGranted('ROLE_ADMIN'))
        {
            return $this->render('funcionario/index.html.twig', [
                'funcionarios' => $lt,
            ]);
        }
        else
        {
            return $this->render('funcionario/index_clientes.html.twig', [
                'funcionarios' => $lt,
            ]);
        }
    }

    /**
     * @Route("/new", name="funcionario_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, UserPasswordHasherInterface $uph, LicensingHelper $licHelper): Response
    {
        $pFuncionario = new PerfilFuncionario();
        $form = $this->createForm(PerfilType::class, $pFuncionario, ['operacao' => 'new']);
        $form->handleRequest($request);
        // TODO: incluir seletor de pessoas que já são funcionários. Se for selecionado, redirecionar para manipulação de horário
        if ($form->isSubmitted() && $form->isValid())
        {
            $pFuncionario->setRoles(['ROLE_FUNCIONARIO']);
            $facto = new RandomLib\Factory;
            $gerador = $facto->getLowStrengthGenerator();
            $pFuncionario->setPassword($uph->hashPassword($pFuncionario, $gerador->generateString(15)));

            $empresa = $this->doctrine->getRepository(Empresa::class)->find($request->cookies->get('cnpj'));
            $lt = (new FuncionarioLocalTrabalho())
                ->setCnpj($empresa)
                ->setCpfFuncionario($pFuncionario)
            ;

            $manager = $this->doctrine->getManager();


            $proceed = false;
            try {
                $proceed = $licHelper->validarCotaFuncionarios($request->cookies->get('cnpj'));
            } catch (\Exception $e) {
                $this->addFlash('erro', $e->getMessage());
            }

            if ($proceed)
            {
                $manager->persist($pFuncionario);
                $manager->persist($lt);
                $manager->flush();
                $this->addFlash('sucesso', 'Informações salvas!');
            }
            else
            {
                $this->addFlash('erro', 'Esta empresa atingiu a cota contratada de funcionários');
            }
            return $this->redirectToRoute('funcionario_index');
        }
        return $this->render('perfil/new.html.twig', [
            'form' => $form->createView(),
            'titulo' => 'Novo Funcionário',
            'voltar_path' => 'funcionario_index',
        ]);
    }

    /**
     * @Route("/{nomeUsuario}", name="funcionario_show", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function show(PerfilFuncionario $funcionario): Response
    {
        return $this->render('funcionario/show.html.twig', [
            'funcionario' => $funcionario,
        ]);
    }

    /**
     * @Route("/{nomeUsuario}/edit", name="funcionario_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function edit(Request $request, PerfilFuncionario $funcionario): Response
    {
        $pass = $funcionario->getPassword();
        $form = $this->createForm(PerfilType::class, $funcionario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $funcionario->setPassword($pass);
            $this->doctrine->getManager()->flush();
            $this->addFlash('sucesso', 'Informações salvas!');
            return $this->redirectToRoute('funcionario_index');
        }

        return $this->render('perfil/new.html.twig', [
            'funcionario' => $funcionario,
            'operacao' => 'editar',
            'form' => $form->createView(),
            'titulo' => 'Editar Funcionário',
            'voltar_path' => 'funcionario_index'
        ]);
    }

    /**
     * @Route("/{nomeUsuario}", name="funcionario_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, PerfilFuncionario $funcionario): Response
    {
        if ($this->isCsrfTokenValid('delete' . $funcionario->getPessoa()->getCpf(), $request->request->get('_token'))) {
            $doc = $this->doctrine;
            $lt = $doc->getRepository(FuncionarioLocalTrabalho::class)->findOneBy(['cnpj' => $request->cookies->get('cnpj'), 'cpfFuncionario' => $funcionario]);
            $lt->setAtivo(false);
            $doc->getManager()->flush();
            $this->addFlash('sucesso', 'Informações salvas!');
        }

        return $this->redirectToRoute('funcionario_index');
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
