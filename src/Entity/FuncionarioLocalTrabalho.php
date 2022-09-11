<?php

namespace App\Entity;

use App\Repository\FuncionarioLocalTrabalhoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * FuncionarioLocalTrabalho
 *
 * @ORM\Entity(repositoryClass=FuncionarioLocalTrabalhoRepository::class)
 * @UniqueEntity(fields={"cnpj", "cpfFuncionario"}, message="Esta pessoa já trabalha aqui.")
 */

class FuncionarioLocalTrabalho
{
    /**
     * @var Empresa
     *
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="funcionarios")
     * @ORM\JoinColumn(referencedColumnName="cnpj", nullable=false)
     * @ORM\Id
     */
    private $cnpj;

    /**
     * @var PerfilFuncionario
     *
     * @ORM\OneToOne(targetEntity=PerfilFuncionario::class, inversedBy="funcionarioLocalTrabalho")
     * @ORM\JoinColumn(referencedColumnName="nome_usuario", nullable=false)
     * @ORM\Id
     */
    private $cpfFuncionario;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable = false)
     */
    private $ativo = true;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", nullable=true)
     * @Assert\PositiveOrZero(
     *      message = "Defina um valor maior ou igual a zero."
     * )
     */
    private $salario;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", nullable=true)
     * @Assert\PositiveOrZero(
     *      message = "Defina um valor maior ou igual a zero."
     * )
     */
    private $comissao;

    /**
     * @ORM\Column(type="json")
     */
    private $privilegios = [];

    public function getCnpj(): ?Empresa
    {
        return $this->cnpj;
    }

    public function setCnpj(Empresa $cnpj): self
    {
        if(!isset($this->cnpj))
        {
            $this->cnpj = $cnpj;
        }

        return $this;
    }

    public function getCpfFuncionario(): ?PerfilFuncionario
    {
        return $this->cpfFuncionario;
    }

    public function setCpfFuncionario(?PerfilFuncionario $cpfFuncionario): self
    {
        $this->cpfFuncionario = $cpfFuncionario;
        return $this;
    }

    public function getAtivo(): ?bool
    {
        return $this->ativo;
    }

    public function setAtivo(bool $ativo): self
    {
        $this->ativo = $ativo;

        return $this;
    }

    public function getSalario(): ?float
    {
        return $this->salario;
    }

    public function setSalario(float $salario): self
    {
        $this->salario = $salario;

        return $this;
    }

    public function getComissao(): ?float
    {
        return $this->comissao;
    }

    public function setComissao(float $comissao): self
    {
        $this->comissao = $comissao;

        return $this;
    }

    public function getPrivilegios(): ?array
    {
        return $this->privilegios;
    }

    public function setPrivilegios(array $privilegios): self
    {
        $this->privilegios = $privilegios;

        return $this;
    }
}