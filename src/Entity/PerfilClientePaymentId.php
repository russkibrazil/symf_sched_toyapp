<?php

namespace App\Entity;

use App\Repository\PerfilClientePaymentIdRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PerfilClientePaymentIdRepository::class)
 */
class PerfilClientePaymentId
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $processador;

    /**
     * @ORM\ManyToOne(targetEntity=PerfilCliente::class, inversedBy="perfilClientePaymentIds")
     * @ORM\JoinColumn(referencedColumnName="nome_usuario", nullable=false)
     */
    private $perfilCliente;

    /**
     * @ORM\Column(type="json")
     */
    private $cards = [];

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPerfilCliente(): ?PerfilCliente
    {
        return $this->perfilCliente;
    }

    public function setPerfilCliente(?PerfilCliente $perfilCliente): self
    {
        $this->perfilCliente = $perfilCliente;

        return $this;
    }

    public function getCards(): ?array
    {
        return $this->cards;
    }

    public function setCards(array $cards): self
    {
        $this->cards = $cards;

        return $this;
    }
}
