<?php

namespace App\Entity;

use DateTime;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Validator as AppValidators;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * Undocumented class
 *
 * @ORM\Entity
 * @ORM\Table("perfil", indexes={@ORM\Index(name="email_profile_index", fields={"email"})})
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator", type="string")
 * @ORM\DiscriminatorMap({"client" = "PerfilCliente", "employee" = "PerfilFuncionario"})
 */
class Perfil implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Column(type="uuid")
     */
    private $uid;

    /**
     * Nome de usuário para acessar a plataforma
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=25)
     * @var string
     * Ultimo commit problemático 512bb000944f770ce9a904aa6c0e3ae52cc73009
     */
    protected $nomeUsuario;

    /**
     * @ORM\Column(type="string", length=180, unique=false)
     * @Assert\Email(
     *      message = "O endereço de e-mail não é válido."
     *  )
     */
    private $email;

    /**
     * Nome do arquivo gerado pelo Vich representando imagem deste perfil
     *
     * @var string|null
     */
    private $foto;

    /**
     * @Vich\UploadableField(mapping="usuario_foto", fileNameProperty="foto")
     *
     * @var File|null
     */
    private $arquivoFoto;

    /**
     * @ORM\Column(type="string", length=180)
     */
    protected $password;

    /**
     * @ORM\Column(type="json")
     */
    protected $roles = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $confirmado;

    /**
     * Undocumented variable
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTimeInterface
     */
    private $atualizadoEm;

    protected ?string $discriminator = null;

    public function __construct()
    {
        $this->uid = Uuid::v4();
        $this->atualizadoEm = new DateTime();
        $this->confirmado = false;
    }

    public function getUid(): ?Uuid
    {
        return $this->uid;
    }

    public function getNomeUsuario(): ?string
    {
        return $this->nomeUsuario;
    }

    public function setNomeUsuario(string $nomeUsuario): self
    {
        $this->nomeUsuario = $nomeUsuario;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFoto(): ?string
    {
        return $this->foto;
    }

    public function setFoto(?string $foto): self
    {
        $this->foto = $foto;

        return $this;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $arquivoFoto
     */
    public function setArquivoFoto(?File $arquivoFoto = null): void
    {
        $this->arquivoFoto = $arquivoFoto;

        if (null !== $arquivoFoto) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getArquivoFoto(): ?File
    {
        return $this->arquivoFoto;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): ?array
    {
        return $this->roles === [] ? ['ROLE_USER'] : $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getConfirmado(): ?bool
    {
        return $this->confirmado;
    }

    public function setConfirmado(bool $confirmado): self
    {
        $this->confirmado = $confirmado;

        return $this;
    }

    public function getAtualizadoEm(): ?\DateTimeInterface
    {
        return $this->atualizadoEm;
    }

    public function setAtualizadoEm(\DateTimeInterface $dh): self
    {
        $this->atualizadoEm = $dh;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->confirmado;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->confirmado = $isVerified;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return $this->nomeUsuario;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->nomeUsuario;
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(){}
}
