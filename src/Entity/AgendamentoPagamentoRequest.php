<?php

namespace App\Entity;

use App\Repository\AgendamentoPagamentoRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AgendamentoPagamentoRequestRepository::class)
 * @UniqueEntity("agendamento")
 */
class AgendamentoPagamentoRequest
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=20, unique=true)
     */
    private $token;

    /**
     * @ORM\OneToOne(targetEntity=Agendamento::class)
     * @ORM\JoinColumn(nullable=false, unique=true)
     */
    private $agendamento;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\Expression(
     *      "value.getTimestamp() > time()",
     *      message = "Margem de tempo insuficiente"
     * )
     */
    private $validade;

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
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

    public function getValidade(): ?\DateTimeInterface
    {
        return $this->validade;
    }

    public function setValidade(\DateTimeInterface $validade): self
    {
        $this->validade = $validade;

        return $this;
    }
}
