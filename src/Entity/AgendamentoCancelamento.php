<?php

namespace App\Entity;

use App\Repository\AgendamentoCancelamentoRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AgendamentoCancelamentoRepository::class)
 */
class AgendamentoCancelamento
{
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity=Agendamento::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false, unique=true)
     */
    private $agendamento;

    /**
     * @ORM\Column(type="datetime")
     */
    private $cancelledTs;

    /**
     * @ORM\Column(type="text")
     */
    private $reason;

    /**
     * @ORM\ManyToOne(targetEntity=Perfil::class)
     * @ORM\JoinColumn(referencedColumnName="nome_usuario", nullable=false)
     */
    private $requestedBy;

    public function __construct()
    {
        $this->cancelledTs = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgendamento(): ?Agendamento
    {
        return $this->agendamento;
    }

    public function setAgendamento(Agendamento $agendamento): self
    {
        $this->agendamento = $agendamento;

        return $this;
    }

    public function getCancelledTs(): ?\DateTimeInterface
    {
        return $this->cancelledTs;
    }

    public function setCancelledTs(\DateTimeInterface $cancelledTs): self
    {
        $this->cancelledTs = $cancelledTs;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public static function defaultCancellingReasons(): array
    {
        return array(
            "Remarcar",
            "Desistência do cliente",
            "Desistência do prestador",
            "Força maior",
            "Problema com materiais ou equipamentos",
            "Sem garantia de pagamento"
        );
    }

    public function getRequestedBy(): ?Perfil
    {
        return $this->requestedBy;
    }

    public function setRequestedBy(?Perfil $requestedBy): self
    {
        $this->requestedBy = $requestedBy;

        return $this;
    }
}
