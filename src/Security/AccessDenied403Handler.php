<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDenied403Handler implements AccessDeniedHandlerInterface
{
    private $gen;

    public function __construct(UrlGeneratorInterface $urlGen)
    {
        $this->gen = $urlGen;
    }
    public function handle(Request $request, AccessDeniedException $e): ?Response
    {
        $request->getSession()->getFlashBag()->add('erro', 'O usuário não tem privilégios suficientes');
        $ref = $request->request->get('referer', $this->gen->generate('home'));
        return new RedirectResponse($ref);
    }
}
