<?php

namespace App\Entity;

use App\Repository\AgendamentoPagamentoRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AgendamentoPagamentoRepository::class)
 */
class AgendamentoPagamento
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Agendamento::class, inversedBy="agendamentoPagamentos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $agendamento;

    /**
     * @ORM\Column(type="datetime")
     */
    private $data;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $formaPagto;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     * @Assert\Positive(
     *      message = "Para registrar o pagamento, é necessário digitar um valor maior que zero."
     * )
     */
    private $valor;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $statusAtual;

    /**
     * @ORM\Column(type="datetime")
     */
    private $ultima_modificacao;

    /**
     * @ORM\Column(type="boolean")
     */
    private $capturado = true;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $processador;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $log = [];

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->data = new DateTime();
        $this->ultima_modificacao = $this->data;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getAgendamento(): ?Agendamento
    {
        return $this->agendamento;
    }

    public function setAgendamento(?Agendamento $Agendamento): self
    {
        $this->agendamento = $Agendamento;

        return $this;
    }

    public function getData(): ?\DateTimeInterface
    {
        return $this->data;
    }

    public function setData(\DateTimeInterface $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getFormaPagto(): ?string
    {
        return $this->formaPagto;
    }

    public function setFormaPagto(string $forma_pagto): self
    {
        $this->formaPagto = $forma_pagto;

        return $this;
    }

    public function getValor(): ?string
    {
        return $this->valor;
    }

    public function setValor(string $valor): self
    {
        $this->valor = $valor;

        return $this;
    }

    public function getStatusAtual(): ?string
    {
        return $this->statusAtual;
    }

    public function setStatusAtual(string $statusAtual): self
    {
        $this->statusAtual = $statusAtual;

        return $this;
    }

    public function getUltimaModificacao(): ?\DateTimeInterface
    {
        return $this->ultima_modificacao;
    }

    public function setUltimaModificacao(\DateTimeInterface $ultima_modificacao): self
    {
        $this->ultima_modificacao = $ultima_modificacao;

        return $this;
    }

    public function getCapturado(): ?bool
    {
        return $this->capturado;
    }

    public function setCapturado(bool $capturado): self
    {
        $this->capturado = $capturado;

        return $this;
    }

    public function getProcessador(): ?string
    {
        return $this->processador;
    }

    public function setProcessador(string $processador): self
    {
        $this->processador = $processador;

        return $this;
    }

    public function getLog(): ?array
    {
        return $this->log;
    }

    public function setLog(?array $log): self
    {
        $this->log = $log;

        return $this;
    }
}
