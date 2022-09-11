<?php

namespace App\Entity;

use App\Repository\PerfilClienteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=PerfilClienteRepository::class)
 * @Vich\Uploadable
 * @UniqueEntity(fields={"nomeUsuario"}, message="Já existe uma conta com este nome de usuário.")
 */
class PerfilCliente extends Perfil
{
    public function __construct()
    {
        parent::__construct();
        $this->agendamentosC = new ArrayCollection();
        $this->usuarioReputacao = new ArrayCollection();
        $this->perfilClientePaymentIds = new ArrayCollection();
        $this->discriminator = 'client';
    }

    /**
     * @ORM\OneToOne(targetEntity=Pessoa::class, inversedBy="perfilCliente", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="cpf", nullable=false, unique=false)
     */
    private $pessoa;

    /**
     * @return Collection|Agendamento[]
     * @ORM\OneToMany(targetEntity=Agendamento::class, mappedBy="cliente")
     */
    private $agendamentosC;

    /**
     * Registros de conduta do usuário
     * @ORM\OneToMany(targetEntity=ClienteAvaliacao::class, mappedBy="cpf", cascade={"persist"})
     * @return Collection|ClienteAvaliacao[]
     */
    private $usuarioReputacao;

    /**
     * @ORM\OneToMany(targetEntity=PerfilClientePaymentId::class, mappedBy="perfilCliente", orphanRemoval=true)
     */
    private $perfilClientePaymentIds;

    public function getPessoa(): ?Pessoa
    {
        return $this->pessoa;
    }

    public function setPessoa(?Pessoa $pessoa): self
    {
        $this->pessoa = $pessoa;

        return $this;
    }

    public function getAgendamentos(): Collection
    {
        return $this->agendamentosC;
    }

    /**
     * @return Collection|ClienteAvaliacao[]
     */
    public function getUsuarioReputacao(): Collection
    {
        return $this->usuarioReputacao;
    }

    public function addUsuarioReputacao(ClienteAvaliacao $usuarioReputacao): self
    {
        if (!$this->usuarioReputacao->contains($usuarioReputacao)) {
            $this->usuarioReputacao[] = $usuarioReputacao;
            $usuarioReputacao->setCpf($this);
        }

        return $this;
    }

    public function removeUsuarioReputacao(ClienteAvaliacao $usuarioReputacao): self
    {
        if ($this->usuarioReputacao->contains($usuarioReputacao)) {
            $this->usuarioReputacao->removeElement($usuarioReputacao);
            $usuarioReputacao->setCnpj(null);
            $usuarioReputacao->setCpf(null);
        }

        return $this;
    }

    /**
     * @return Collection|PerfilClientePaymentId[]
     */
    public function getPerfilClientePaymentIds(): Collection
    {
        return $this->perfilClientePaymentIds;
    }

    public function addPerfilClientePaymentId(PerfilClientePaymentId $perfilClientePaymentId): self
    {
        if (!$this->perfilClientePaymentIds->contains($perfilClientePaymentId)) {
            $this->perfilClientePaymentIds[] = $perfilClientePaymentId;
            $perfilClientePaymentId->setPerfilCliente($this);
        }

        return $this;
    }

    public function removePerfilClientePaymentId(PerfilClientePaymentId $perfilClientePaymentId): self
    {
        if ($this->perfilClientePaymentIds->removeElement($perfilClientePaymentId)) {
            // set the owning side to null (unless already changed)
            if ($perfilClientePaymentId->getPerfilCliente() === $this) {
                $perfilClientePaymentId->setPerfilCliente(null);
            }
        }

        return $this;
    }
}