<?php

namespace App\EventSubscriber\LoginSuccessEvent;

use App\Entity\RegistroAcesso;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginConcedidoSubscriber implements EventSubscriberInterface
{
    private $em;
    private $urlGen;

    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $urlGeneratorInterface)
    {
        $this->em = $em;
        $this->urlGen = $urlGeneratorInterface;
    }

    public function onLoginSuccessEvent(LoginSuccessEvent $event)
    {
        $ip = IpUtils::anonymize($event->getRequest()->getClientIp());
        $usuario = $event->getUser();

        $registro = (new RegistroAcesso())
            ->setOrigem($ip)
            ->setUsuario($usuario)
        ;

        $this->em->persist($registro);
        $this->em->flush();

        // $statusPrivacidade = stat(dirname(__DIR__,2) . '/public/privacy.md');
        // /**
        //  * @var \DateTimeInterface $dhAceite
        //  */
        // $dhAceite = $usuario->getAceiteTermos() instanceof DateTimeInterface ? $usuario->getAceiteTermos() : new DateTime('@0');

        // if ($dhAceite->getTimestamp() < $statusPrivacidade['mtime'])
        // {
        //     $originalTargetPath = $event->getRequest()->getSession()->get('_security.main.target_path', $this->urlGen->generate('home'));
        //     $novoTargetPath = $this->urlGen->generate('aceite_privacidade');
        //     $event->getRequest()->getSession()->set('_security.main.target_path', $novoTargetPath);

        //     $newResponse = new RedirectResponse($this->urlGen->generate('aceite_privacidade'));
        //     $newResponse->headers->setCookie(new Cookie('target_path', $originalTargetPath, time() + 360, $novoTargetPath));
        //     $event->setResponse($newResponse);
        // }
    }

    public static function getSubscribedEvents()
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccessEvent',
        ];
    }
}
