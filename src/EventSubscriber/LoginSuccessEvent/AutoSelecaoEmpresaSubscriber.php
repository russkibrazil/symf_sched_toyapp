<?php

namespace App\EventSubscriber\LoginSuccessEvent;

use App\Entity\Empresa;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AutoSelecaoEmpresaSubscriber implements EventSubscriberInterface
{
    private $doctrine;
    private $urlGen;

    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $urlGeneratorInterface)
    {
        $this->doctrine = $em;
        $this->urlGen = $urlGeneratorInterface;
    }
    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
        if (null === $event->getRequest()->cookies->get('cnpj')) {
            $u = $event->getUser();
            $roles = $u->getRoles();
            $arrayEmpresas = $this->doctrine->getRepository(Empresa::class)->findAll();
            $nEmpresas = count($arrayEmpresas);
            if ($nEmpresas === 1)
            {
                $novoResponse = $event->getResponse();
                $cnpj = $arrayEmpresas[0]->getCnpj();
                $cookie = new Cookie('cnpj', $cnpj);
                $novoResponse->headers->setCookie($cookie);
                $event->setResponse($novoResponse);
                return;
            }
            if (in_array('ROLE_USER', $roles) || in_array('ROLE_PROPRIETARIO', $roles)) {
                $novoResponse = new RedirectResponse($this->urlGen->generate('empresa_selecao_inicial'));
                $event->setResponse($novoResponse);
                return;
            }

            $lt = $u->getFuncionarioLocalTrabalho();
            if ($lt == null)
                return;

            $novoResponse = $event->getResponse();
            $cnpj = $lt->getCnpj()->getCnpj();
            $cookie = new Cookie('cnpj', $cnpj);
            $novoResponse->headers->setCookie($cookie);
            $event->setResponse($novoResponse);
            return;
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccessEvent',
        ];
    }
}
