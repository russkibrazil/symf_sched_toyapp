<?php

namespace App\EventListener\Doctrine;

use App\Entity\PerfilFuncionario;
use App\Event\MaxQuotaFuncionariosEvent;
use App\Service\LicensingHelper;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class LicenciamentoFuncionario
{
    private $dispatcher;
    private $licensingSvc;

    public function __construct(EventDispatcherInterface $ed, LicensingHelper $licensingHelper)
    {
        $this->dispatcher = $ed;
        $this->licensingSvc = $licensingHelper;
    }

    public function prePersist(PerfilFuncionario $perfil, LifecycleEventArgs $event): void
    {
        $em = $event->getEntityManager();
        if ($this->licensingSvc->validarCotaFuncionarios())
        {
            $em->remove($perfil);
            $em->detach($perfil);
            $uow = $em->getUnitOfWork();
            if ($uow->isScheduledForInsert($perfil))
            {
                $uow->remove($perfil);
            }
            $e = new MaxQuotaFuncionariosEvent();
            $this->dispatcher->dispatch($e, MaxQuotaFuncionariosEvent::NAME);
        }
    }
}
