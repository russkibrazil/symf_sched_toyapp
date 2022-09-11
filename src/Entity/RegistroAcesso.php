<?php

namespace App\Entity;

use App\Repository\RegistroAcessoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=RegistroAcessoRepository::class)
 */
class RegistroAcesso
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $reg;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $origem;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dh;

    /**
     * @ORM\ManyToOne(targetEntity=Perfil::class)
     * @ORM\JoinColumn(referencedColumnName="nome_usuario", nullable=false)
     */
    private $usuario;

    public function __construct()
    {
        $this->reg = Uuid::v4();
        $this->dh = new \DateTime();
    }

    public function getReg(): ?Uuid
    {
        return $this->reg;
    }

    public function getOrigem(): ?string
    {
        return $this->origem;
    }

    public function setOrigem(string $origem): self
    {
        $this->origem = $origem;

        return $this;
    }

    public function getDh(): ?\DateTimeInterface
    {
        return $this->dh;
    }

    public function getUsuario(): ?Perfil
    {
        return $this->usuario;
    }

    public function setUsuario(UserInterface $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }
}
