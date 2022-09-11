<?php

namespace App\Controller;

use RandomLib;
use App\Entity\User;
use App\Entity\Empresa;
use App\Form\PerfilType;
use App\Form\UserApagarType;
use App\Entity\PerfilCliente;
use App\Entity\ClienteAvaliacao;
use App\Entity\Pessoa;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @Route("/cliente")
 */
class ClienteController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $mr)
    {
        $this->doctrine = $mr;
    }

    public function index(): Response
    {
        $clientes = $this->doctrine
            ->getRepository(User::class)
            ->findAll();

        return $this->render('cliente/index.html.twig', [
            'clientes' => $clientes,
        ]);
    }

    /**
     * @Route("/new", name="cliente_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_FUNCIONARIO') or is_granted('ROLE_ADMIN')")
     */
    public function new(Request $request, UserPasswordHasherInterface $passwordEncoder): Response
    {
        $op = 'new';
        $cliente = new PerfilCliente();
        $form = $this->createForm(PerfilType::class, $cliente, ['operacao' => $op]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $facto = new RandomLib\Factory;
            $gerador = $facto->getLowStrengthGenerator();
            $cliente->setPassword($passwordEncoder->hashPassword($cliente, $gerador->generateString(15)));
            $entityManager = $this->doctrine->getManager();
            $emp = $this->doctrine->getRepository(Empresa::class)->find($request->cookies->get('cnpj'));
            try
            {
                $repSheet = (new ClienteAvaliacao())
                    ->setCnpj($emp)
                    ->setCpf($cliente)
                ;
                $cliente->addUsuarioReputacao($repSheet);
                $entityManager->persist($cliente);
                $entityManager->flush();
                $this->addFlash('sucesso', 'Informações salvas! Para o cliente começar a usar, ele deve recuperar a senha na tela de login usando a função \"Esqueci a senha\".');
            }
            catch (UniqueConstraintViolationException $err)
            {
                $this->addFlash('erro', 'Este CPF já está cadastrado na plataforma!');
            }
            finally
            {
                return $this->redirectToRoute('agendamentos_index');
            }
        }

        return $this->render('perfil/new.html.twig', [
            'form' => $form->createView(),
            'titulo' => 'Novo Cliente',
            'voltar_path' => 'agendamentos_index'
        ]);
    }

    /**
     * @Route("/{nomeUsuario}", name="cliente_show", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function show(PerfilCliente $cliente): Response
    {
        return $this->render('cliente/show.html.twig', [
            'cliente' => $cliente,
        ]);
    }

    /**
     * @Route("/{nomeUsuario}/edit", name="cliente_edit", methods={"GET","POST"})
     * @Security("(user.getUserIdentifier() == cliente.getNomeUsuario()) or is_granted('ROLE_RECEPCAO')")
     */
    public function edit(Request $request, PerfilCliente $cliente): Response
    {
        $pass = $cliente->getPassword();
        $form = $this->createForm(PerfilType::class, $cliente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cliente->setPassword($pass);
            $this->doctrine->getManager()->flush();
            $this->addFlash('sucesso', 'Informações salvas!');
            return $this->redirectToRoute('cliente_show', ['nomeUsuario' => $cliente->getNomeUsuario()]);
        }

        return $this->render('perfil/new.html.twig', [
            'funcionario' => $cliente,
            'operacao' => 'editar',
            'form' => $form->createView(),
            'titulo' => 'Editar Perfil',
            'voltar_path' => 'home'
        ]);
    }
    /**
     * @Route("/{nomeUsuario}/apagar", name="cliente_apagar_conta", methods={"GET", "POST"})
     * @Security("user.getUserIdentifier() == cliente.getNomeUsuario()")
     */
    public function delete(Request $request, PerfilCliente $cliente): Response
    {
        $form = $this->createForm(UserApagarType::class, null, ['email' => $cliente->getEmail()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var \App\Repository\PessoaRepository $uRep
             */
            $uRep = $this->doctrine->getRepository(Pessoa::class);
            $uRep->apagarUsuario($cliente);
            $this->addFlash('sucesso', 'Conta removida');
            return $this->redirectToRoute('home');
        }

        $hasPerfilProfissional = $cliente->getPessoa()->getPerfilFuncionarios() === [] ? false : true;
        return $this->render('cliente/apagar_conta.html.twig', [
            'form' => $form->createView(),
            'hasPerfilProfissional' => $hasPerfilProfissional,
        ]);
    }
}
