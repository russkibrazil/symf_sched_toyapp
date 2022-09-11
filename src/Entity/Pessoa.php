<?php

namespace App\Entity;

use App\Repository\PessoaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=PessoaRepository::class)
 * @UniqueEntity(fields={"cpf"}, message="Esta pessoa já foi cadastrada.")
 */
class Pessoa
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=11)
     * @Assert\Regex(
     *  pattern = "/^\d{11}/",
     *  htmlPattern = "[0-9]{11}",
     *  message = "O CPF não foi digitado corretamente. Não utilize pontos e traços."
     * )
     */
    private $cpf;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "O nome é muito curto.",
     *      maxMessage = "O nome é muito longo.",
     *      allowEmptyString = false
     * )
     */
    private $nome;

    /**
     * @ORM\Column(type="string", length=11)
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 10,
     *      max = 11,
     *      minMessage = "O telefone é muito curto. O DDD deve ser incluído.",
     *      maxMessage = "O valor é muito longo.",
     *      allowEmptyString = false
     * )
     */
    private $telefone;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     * @Assert\Length(
     *      max = 180,
     *      maxMessage = "O endereço é muito longo.",
     *      allowEmptyString = true
     * )
     */
    private $endereco;

    /**
     * Identificador de usuário para não expor CPF
     *
     * @ORM\Column(type="uuid", unique=true)
     *
     * @var UUID
     */
    private $uid;

    /**
     * @ORM\OneToOne(targetEntity=PerfilCliente::class, mappedBy="pessoa", orphanRemoval=true)
     */
    private $perfilCliente;

    /**
     * @var Collection|PerfilFuncionario[]
     * @ORM\OneToMany(targetEntity=PerfilFuncionario::class, mappedBy="pessoa", orphanRemoval=true)
     */
    private $perfilFuncionario;

    public function __construct()
    {
        $this->perfilFuncionario = new ArrayCollection();
    }

    public function getCpf(): ?string
    {
        return $this->cpf;
    }

    public function setCpf(string $cpf): self
    {
        $this->cpf = $cpf;
        $this->uid = Uuid::v5(Uuid::fromString(Uuid::NAMESPACE_URL), "pessoa/$cpf");
        return $this;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): self
    {
        $this->nome = $nome;

        return $this;
    }

    public function getTelefone(): ?string
    {
        return $this->telefone;
    }

    public function setTelefone(string $telefone): self
    {
        $this->telefone = $telefone;

        return $this;
    }

    public function getEndereco(): ?string
    {
        return $this->endereco;
    }

    public function setEndereco(?string $endereco): self
    {
        $this->endereco = $endereco;

        return $this;
    }

    public function getUid(): Uuid
    {
        return $this->uid;
    }

    public function getPerfilCliente(): ?PerfilCliente
    {
        return $this->perfilCliente;
    }

    public function setPerfilCliente(PerfilCliente $pc): self
    {
        $this->perfilCliente = $pc;

        return $this;
    }

    /**
     * @return Collection|PerfilFuncionario[]
     */
    public function getPerfilFuncionarios(): Collection
    {
        return $this->perfilFuncionario;
    }

    public function addPerfilFuncionario(PerfilFuncionario $perfil): self
    {
        if (!$this->perfilFuncionario->contains($perfil)) {
            $this->perfilFuncionario[] = $perfil;
            $perfil->setPessoa($this);
        }

        return $this;
    }

    public function removePerfilFuncionario(PerfilFuncionario $perfil): self
    {
        if ($this->perfilFuncionario->removeElement($perfil)) {
            // set the owning side to null (unless already changed)
            if ($perfil->getPessoa() === $this) {
                $perfil->setPessoa(null);
            }
        }

        return $this;
    }
}
