<?php

namespace App\EventSubscriber\LogoutEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class PurgadorCookiesSubscriber implements EventSubscriberInterface
{
    public function onLogoutEvent(LogoutEvent $event)
    {
        $resp = $event->getResponse();
        $resp->headers->clearCookie('cnpj');
        $event->setResponse($resp);
    }

    public static function getSubscribedEvents()
    {
        return [
            LogoutEvent::class => 'onLogoutEvent',
        ];
    }
}
