<?php

namespace App\Controller;

use App\Entity\ClienteAvaliacao;
use App\Entity\Empresa;
use App\Entity\PerfilCliente;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()) {
            $this->addFlash('erro', 'Você já está registrado');
            return $this->redirectToRoute('home');
        }

        $user = new PerfilCliente();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            try {
                $entityManager->persist($user);
                $entityManager->flush();

                if ($cnpj = $request->cookies->get('cnpj') ?? $request->query->get('emp'))
                {
                    $empresa = $entityManager->getRepository(Empresa::class)->find($cnpj);
                    if ($empresa)
                    {
                        $ficha = (new ClienteAvaliacao())
                            ->setCpf($user)
                            ->setCnpj($empresa)
                        ;
                        $entityManager->persist($ficha);
                        $entityManager->flush();
                    }
                }
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('no-reply@oddbox.com.br', 'Iroko'))
                        ->to($user->getEmail())
                        ->subject('Confirmação de Cadastro -- Iroko')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );
            } catch (UniqueConstraintViolationException $err) {
                $this->addFlash('erro', 'Este CPF já está cadastrado na plataforma!');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            } catch (TransportException $th) {
                $this->addFlash('erro', 'Encontramos problemas para enviar o e-mail de confirmação.');
                return $this->redirectToRoute('home');
            }
            $this->addFlash('sucesso', 'Verfique seu e-mail para os próximos passos!');
            return $this->redirectToRoute('home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('sucesso', 'Sua conta foi verificada com sucesso.');

        return $this->redirectToRoute('home');
    }
}
