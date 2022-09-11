<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $cnpj = $request->query->get('emp');
        $arrayOptions = [
            'last_username' => $lastUsername,
            'error' => $error,
            'cnpj' => $cnpj,
            'hcaptcha_sitekey' => $_ENV['HCAPTCHA_SITE_KEY'],
        ];

        if (isset($cnpj)) {
            $r = $this->render('security/login.html.twig', $arrayOptions);
            $r->headers->setCookie(Cookie::create('cnpj', $cnpj));
        } else {
            $cnpj = $request->cookies->get('cnpj');
            $r = $this->render('security/login.html.twig', $arrayOptions);
        }
        return $r;
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {}

    /**
     * Route for authenticate hcaptcha tokens recieved from user
     * @Route("/hcaptcha", name="hcaptcha_check", methods={"POST"})
     * @param Request $request
     * @param HttpClientInterface $httpClient
     * @return JsonResponse
     */
    public function checkhcaptcha(Request $request, HttpClientInterface $httpClient): JsonResponse
    {
        $sentData = $request->toArray();
        $antibotResponse = $httpClient->request(
            'POST',
            'https://hcaptcha.com/siteverify',
            [
                'body' => 'response=' . $sentData['h-captcha-response'] . '&secret=' . $_ENV['HCAPTCHA_SECRET'] . '&sitekey=' . $_ENV['HCAPTCHA_SITE_KEY'],
            ]
        );

        if (($antibotResponse->toArray())['success'] === true)
        {
            return new JsonResponse();
        }
        return new JsonResponse(null, 403);
    }
}
