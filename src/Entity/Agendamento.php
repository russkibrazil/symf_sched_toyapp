<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Agendamento
 *
 * @ORM\Entity(repositoryClass="App\Repository\AgendamentoRepository")
 */
class Agendamento
{
    /**
     * @var int
     *
     * @ORM\Column(type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $horario;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $compareceu = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $atrasado = false;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $cancelado;

    /**
     * @var string
     * @todo ENUM Doctrine: https://www.doctrine-project.org/projects/doctrine-orm/en/2.9/cookbook/mysql-enums.html
     * @ORM\Column(type="string", length=10, nullable=false, options={"default"="Dinheiro"})
     */
    private $formaPagto = 'Dinheiro';

    /**
     * @var PerfilCliente
     *
     * @ORM\ManyToOne(targetEntity=PerfilCliente::class, inversedBy="agendamentosC")
     * @ORM\JoinColumn(referencedColumnName="nome_usuario", nullable=false)
     *
     * @Assert\NotEqualTo(propertyPath="funcionario")
     */
    private $cliente;

    /**
     * @var Empresa
     * @ORM\ManyToOne(targetEntity=Empresa::class)
     * @ORM\JoinColumn(referencedColumnName="cnpj", nullable=false)
     */
    private $empresa;

    /**
     * @var PerfilFuncionario
     *
     * @ORM\ManyToOne(targetEntity=PerfilFuncionario::class, inversedBy="agendamentosF")
     * @ORM\JoinColumn(referencedColumnName="nome_usuario", nullable=false)
     *
     */
    private $funcionario;

    /**
     * Tempo calculado para a conclusão do agendamento, dado os serviços selecionados e o horário de início
     *
     * @var \Datetime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $conclusaoEsperada;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $concluido = false;

    /**
     * @return Collection|AgendamentoServicos[]
     * @ORM\OneToMany(targetEntity=AgendamentoServicos::class, mappedBy="agendamento", cascade={"persist"}, orphanRemoval=true)
     */
    private $servicos;

    /**
     * @ORM\OneToMany(targetEntity=AgendamentoPagamento::class, mappedBy="agendamento")
     */
    private $agendamentoPagamentos;

    /**
     * @ORM\Column(type="boolean", options={"default"="1"})
     */
    private $pagamentoPendente = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $pagamentoPresencial;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->servicos = new ArrayCollection();
        $this->agendamentoPagamentos = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Undocumented function
     *
     * @param boolean $asDateTime
     * @return DateTime|string|null
     */
    public function getHorario(bool $asDateTime = false)
    {
        if (!isset($this->horario))
            return $this->horario;
        return $asDateTime ? $this->horario : $this->horario->format('Y-m-d H:i:s');
    }

    public function setHorario(string $horario): self
    {
        $this->horario = new DateTime($horario);

        return $this;
    }

    public function getCompareceu(): ?bool
    {
        return $this->compareceu;
    }

    public function setCompareceu(bool $compareceu): self
    {
        $this->compareceu = $compareceu;

        return $this;
    }

    public function getAtrasado(): ?bool
    {
        return $this->atrasado;
    }

    public function setAtrasado(bool $atrasado): self
    {
        $this->atrasado = $atrasado;

        return $this;
    }

    public function getCancelado(): ?\DateTimeInterface
    {
        return $this->cancelado;
    }

    public function setCancelado(?\DateTimeInterface $cancelado): self
    {
        $this->cancelado = $cancelado;

        return $this;
    }

    public function getFormaPagto(): ?string
    {
        return $this->formaPagto;
    }

    public function setFormaPagto(string $formaPagto): self
    {
        $this->formaPagto = $formaPagto;

        return $this;
    }

    public function getCliente(): ?PerfilCliente
    {
        return $this->cliente;
    }

    public function setCliente(?PerfilCliente $cliente): self
    {
        $this->cliente = $cliente;

        return $this;
    }

    public function getEmpresa(): ?Empresa
    {
        return $this->empresa;
    }

    public function setEmpresa(Empresa $empresa): self
    {
        $this->empresa = $empresa;

        return $this;
    }

    public function getFuncionario(): ?PerfilFuncionario
    {
        return $this->funcionario;
    }

    public function setFuncionario(?PerfilFuncionario $funcionario): self
    {
        $this->funcionario = $funcionario;

        return $this;
    }

    public function getConclusaoEsperada(): ?\DateTime
    {
        return $this->conclusaoEsperada;
    }

    /**
     * Undocumented function
     *
     * Para padronizar, enviar timestamps no formato ISO (format _c_)
     *
     * @param string|null $conclusaoEsperada
     * @return self
     */
    public function setConclusaoEsperada(?string $conclusaoEsperada): self
    {
        $this->conclusaoEsperada = new DateTime($conclusaoEsperada);

        return $this;
    }

    public function getConcluido(): ?bool
    {
        return $this->concluido;
    }

    public function setConcluido(bool $concluido): self
    {
        $this->concluido = $concluido;

        return $this;
    }

    public function getServicos() : Collection
    {
        return $this->servicos;
    }

    public function addServico (AgendamentoServicos $servicos) : self {
        if (!$this->servicos->contains($servicos)) {
            $this->servicos[] = $servicos;
            $servicos->setAgendamento($this);
        }
        return $this;
    }

    public function removeServico (AgendamentoServicos $servicos): self{
        if ($this->servicos->contains($servicos)) {
            $this->servicos->removeElement($servicos);
            $servicos->setAgendamento(null);
            $servicos->setServico(null);
        }
        return $this;
    }

    /**
     * @return Collection|AgendamentoPagamento[]
     */
    public function getAgendamentoPagamentos(): Collection
    {
        return $this->agendamentoPagamentos;
    }

    public function addAgendamentoPagamento(AgendamentoPagamento $agendamentoPagamento): self
    {
        if (!$this->agendamentoPagamentos->contains($agendamentoPagamento)) {
            $this->agendamentoPagamentos[] = $agendamentoPagamento;
            $agendamentoPagamento->setAgendamento($this);
        }

        return $this;
    }

    public function removeAgendamentoPagamento(AgendamentoPagamento $agendamentoPagamento): self
    {
        if ($this->agendamentoPagamentos->contains($agendamentoPagamento)) {
            $this->agendamentoPagamentos->removeElement($agendamentoPagamento);
            // set the owning side to null (unless already changed)
            if ($agendamentoPagamento->getAgendamento() === $this) {
                $agendamentoPagamento->setAgendamento(null);
            }
        }

        return $this;
    }

    public function getPagamentoPendente(): ?bool
    {
        return $this->pagamentoPendente;
    }

    public function setPagamentoPendente(bool $pagamentoPendente): self
    {
        $this->pagamentoPendente = $pagamentoPendente;

        return $this;
    }

    public function getPagamentoPresencial(): ?bool
    {
        return $this->pagamentoPresencial;
    }

    public function setPagamentoPresencial(bool $pagamentoPresencial): self
    {
        $this->pagamentoPresencial = $pagamentoPresencial;

        return $this;
    }
}
