<?php

namespace App\Entity;

use App\Repository\EmpresaProcessadorPagamentoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=EmpresaProcessadorPagamentoRepository::class)
 */
class EmpresaProcessadorPagamento
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="empresaProcessadorPagamentos")
     * @ORM\JoinColumn(referencedColumnName="cnpj", nullable=false)
     */
    private $empresa;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $processador;

    /**
     * @ORM\Column(type="string", length=36, nullable=true)
     */
    private $pix;

    /**
     * @ORM\Column(type="integer", options={"default"=1})
     * @Assert\Positive
     */
    private $maxParcelasCartao = 1;

    /**
     * @ORM\Column(type="json")
     */
    private $politicaParcelamento = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmpresa(): ?Empresa
    {
        return $this->empresa;
    }

    public function setEmpresa(?Empresa $empresa): self
    {
        $this->empresa = $empresa;

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

    public function getPix(): ?string
    {
        return $this->pix;
    }

    public function setPix(?string $pix): self
    {
        $this->pix = $pix;

        return $this;
    }

    public function getMaxParcelasCartao(): ?int
    {
        return $this->maxParcelasCartao;
    }

    public function setMaxParcelasCartao(int $maxParcelasCartao): self
    {
        $this->maxParcelasCartao = $maxParcelasCartao;

        return $this;
    }

    public function getPoliticaParcelamento(): ?array
    {
        return $this->politicaParcelamento;
    }

    public function setPoliticaParcelamento(array $politicaParcelamento): self
    {
        $this->politicaParcelamento = $politicaParcelamento;

        return $this;
    }
}
