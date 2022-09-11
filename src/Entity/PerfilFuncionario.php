<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\FuncionarioLocalTrabalho;
use Doctrine\Common\Collections\Collection;
use App\Repository\PerfilFuncionarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=PerfilFuncionarioRepository::class)
 * @ORM\EntityListeners({})
 * @Vich\Uploadable
 * @UniqueEntity(fields={"nomeUsuario"}, message="Já existe uma conta com este nome de usuário.")
 */
class PerfilFuncionario extends Perfil
{
    public function __construct()
    {
        parent::__construct();
        $this->funcionarioTurnoTrabalho = new ArrayCollection();
        $this->discriminator = 'employee';
    }

    /**
     * @ORM\ManyToOne(targetEntity=Pessoa::class, inversedBy="perfilFuncionario", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="cpf", nullable=false)
     */
    private $pessoa;

    /**
     * @return FuncionarioLocalTrabalho
     *
     * @ORM\OneToOne(targetEntity=FuncionarioLocalTrabalho::class, mappedBy="cpfFuncionario", cascade={"persist"})
     */
    private $funcionarioLocalTrabalho;

    /**
     * @return Collection|Agendamento[]
     * @ORM\OneToMany(targetEntity=Agendamento::class, mappedBy="funcionario")
     */
    private $agendamentosF;

    /**
     * @return Collection|FuncionarioTurnoTrabalho[]
     * @ORM\OneToMany(targetEntity=FuncionarioTurnoTrabalho::class, mappedBy="cpfFuncionario", cascade={"persist"}, orphanRemoval=true)
     */
    private $funcionarioTurnoTrabalho;

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
        return $this->agendamentosF;
    }

    public function getFuncionarioLocalTrabalho(): ?FuncionarioLocalTrabalho
    {
        return $this->funcionarioLocalTrabalho;
    }

    public function setFuncionarioLocalTrabalho(FuncionarioLocalTrabalho $flt): self
    {
        $this->funcionarioLocalTrabalho = $flt;

        return $this;
    }

    /**
     * @return Collection|FuncionarioTurnoTrabalho[]
     */
    public function getFuncionarioTurnoTrabalho(): Collection
    {
        return $this->funcionarioTurnoTrabalho;
    }

    public function addFuncionarioTurnoTrabalho(FuncionarioTurnoTrabalho $escalaTrabalho): self
    {
        if (!$this->funcionarioTurnoTrabalho->contains($escalaTrabalho)) {
            $this->funcionarioTurnoTrabalho[] = $escalaTrabalho;
            $escalaTrabalho->setcpfFuncionario($this);
        }

        return $this;
    }

    public function removeFuncionarioTurnoTrabalho(FuncionarioTurnoTrabalho $escalaTrabalho): self
    {
        if ($this->funcionarioTurnoTrabalho->contains($escalaTrabalho)) {
            $this->funcionarioTurnoTrabalho->removeElement($escalaTrabalho);
            $escalaTrabalho->setCnpj(null);
            $escalaTrabalho->setCpfFuncionario(null);
        }

        return $this;
    }
}