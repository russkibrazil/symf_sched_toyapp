<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClienteAvaliacao
 *
 * @ORM\Entity
*/
class ClienteAvaliacao
{
    /**
     * @var Empresa
     *
     * @ORM\ManyToOne(targetEntity=Empresa::class)
     * @ORM\JoinColumn(referencedColumnName="cnpj", nullable=false)
     * @ORM\Id
     */
    private $cnpj;

    /**
     * @var PerfilCliente
     *
     * @ORM\ManyToOne(targetEntity=PerfilCliente::class, inversedBy="usuarioReputacao")
     * @ORM\JoinColumn(referencedColumnName="nome_usuario", nullable=false)
     * @ORM\Id
     */
    private $cpf;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"default"=0})
     */
    private $atrasos = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"default"=0})
     */
    private $cancelamentos = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"default"=0})
     */
    private $bloqueios = 0;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default"=0})
     */
    private $bloqueado = 0;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $inicioBloqueio;

    /**
     * @ORM\Column(type="integer", options={"default"=0})
     */
    private $concluido = 0;

    public function getCnpj(): ?Empresa
    {
        return $this->cnpj;
    }

    public function setCnpj(?Empresa $cnpj): self
    {
        if(!isset($this->cnpj))
        {
            $this->cnpj = $cnpj;
        }

        return $this;
    }

    public function getCpf(): ?PerfilCliente
    {
        return $this->cpf;
    }

    public function setCpf(?PerfilCliente $cpf): self
    {
        if(!isset($this->cpf))
        {
            $this->cpf = $cpf;
        }

        return $this;
    }

    public function getAtrasos(): ?int
    {
        return $this->atrasos;
    }

    public function setAtrasos(int $atrasos): self
    {
        $this->atrasos = $atrasos;

        return $this;
    }

    public function novoAtraso(): self
    {
        $this->atrasos += 1;

        return $this;
    }

    public function getCancelamentos(): ?int
    {
        return $this->cancelamentos;
    }

    public function setCancelamentos(int $cancelamentos): self
    {
        $this->cancelamentos = $cancelamentos;

        return $this;
    }

    public function novoCancelamento(): self
    {
        $this->cancelamentos += 1;

        return $this;
    }

    public function getBloqueios(): ?int
    {
        return $this->bloqueios;
    }

    public function setBloqueios(int $bloqueios): self
    {
        $this->bloqueios = $bloqueios;

        return $this;
    }

    public function novoBloqueio(): self
    {
        $this->bloqueios += 1;

        return $this;
    }

    public function getBloqueado(): ?bool
    {
        return $this->bloqueado;
    }

    public function setBloqueado(bool $bloqueado): self
    {
        $this->bloqueado = $bloqueado;

        return $this;
    }

    public function getInicioBloqueio(): ?\DateTimeInterface
    {
        return $this->inicioBloqueio;
    }

    public function setInicioBloqueio(\DateTimeInterface $inicioBloqueio): self
    {
        $this->inicioBloqueio = $inicioBloqueio;

        return $this;
    }

    public function getConcluido(): ?int
    {
        return $this->concluido;
    }

    public function setConcluido(int $concluido): self
    {
        $this->concluido = $concluido;

        return $this;
    }
}