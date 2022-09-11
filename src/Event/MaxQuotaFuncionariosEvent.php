<?php
namespace App\Event;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\EventDispatcher\Event;

class MaxQuotaFuncionariosEvent extends Event
{
    public const NAME = 'license.max_quota';
    // protected $session;

    public function __construct()
    {
        // $this->session = $session;
    }

    // public function getSession(): SessionInterface
    // {
    //     return $this->session;
    // }
}

// https://www.doctrine-project.org/projects/doctrine-orm/en/2.10/reference/events.html#entity-listeners
// https://symfony.com/doc/current/doctrine/events.html#doctrine-entity-listeners
// https://symfony.com/doc/current/components/event_dispatcher.html#creating-and-dispatching-an-event
// https://symfony.com/doc/current/event_dispatcher.html
