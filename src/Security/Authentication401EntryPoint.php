<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class Authentication401EntryPoint implements AuthenticationEntryPointInterface
{
    private $generator;

    public function __construct(UrlGeneratorInterface $gen)
    {
        $this->generator = $gen;
    }

    public function start(Request $request, AuthenticationException $authEx = null): Response
    {
        $request->getSession()->getFlashBag()->add('erro', 'É necessário logar para continuar');
        return new RedirectResponse($this->generator->generate('app_login'));
    }
}