<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Agendamento_Servicos
 * @ORM\Entity
 * @UniqueEntity(fields={"agendamento", "servico"}, message="Serviço já incluído no agendamento")
 */
class AgendamentoServicos
{
    /**
     * Undocumented variable
     *
     * @var int
     * @ORM\Column(type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Agendamento
     *
     * @ORM\ManyToOne(targetEntity=Agendamento::class, inversedBy="servicos")
     */
    private $agendamento;

    /**
     * @var Servico
     *
     * @ORM\ManyToOne(targetEntity=Servico::class)
     */
    private $servico;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private $avaliacaoCliente = -1;

    public function getAvaliacaoCliente(): ?int
    {
        return $this->avaliacaoCliente;
    }

    public function setAvaliacaoCliente(int $avaliacaoCliente): self
    {
        $this->avaliacaoCliente =$avaliacaoCliente;

        return $this;
    }

    public function getAgendamento(): Agendamento
    {
        return $this->agendamento;
    }

    public function setAgendamento(?Agendamento $agendamento): self
    {
        $this->agendamento = $agendamento;

        return $this;
    }

    public function getServico(): ?Servico
    {
        return $this->servico;
    }

    public function setServico(?Servico $servico): self
    {
        $this->servico = $servico;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }
}