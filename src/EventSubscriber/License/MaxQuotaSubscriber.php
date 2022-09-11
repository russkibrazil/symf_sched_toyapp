<?php

namespace App\EventSubscriber\License;

use App\Event\MaxQuotaFuncionariosEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
// use Symfony\Component\HttpFoundation\RedirectResponse;

class MaxQuotaSubscriber implements EventSubscriberInterface
{
    public function onLicenseMaxQuota(MaxQuotaFuncionariosEvent $event)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            'license.max_quota' => 'onLicenseMaxQuota',
        ];
    }
}
