<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Servico
 *
 * @ORM\Entity(repositoryClass="App\Repository\ServicoRepository")
 * @Vich\Uploadable
 */
class Servico
{
    /**
     * @var int
     *
     * @ORM\Column(type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=45, nullable=false)
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 1,
     *      max = 45,
     *      minMessage = "O nome é muito curto.",
     *      maxMessage = "O nome é muito longo.",
     *      allowEmptyString = false
     * )
     */
    private $servico;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", length=255, nullable=true)
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Muito bem descrito, mas que tal resumir um pouco mais?"
     * )
     */
    private $descricao;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=8, scale=2, nullable=false)
     * @Assert\NotBlank
     * @Assert\PositiveOrZero(
     *      message = "Não creio que você queira pagar para o cliente usar seu serviço."
     * )
     */
    private $valor;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    private $foto;

    /**
     * @Vich\UploadableField(mapping="servico_foto", fileNameProperty="foto")
     *
     * @var File|null
     */
    private $arquivoFoto;

    /**
     * @var Empresa
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="servicos")
     * @ORM\JoinColumn(referencedColumnName="cnpj", nullable=false)
     */
    private $empresa;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $ativo = true;

    /**
     * @var Datetime
     *
     * @ORM\Column(type="time", nullable=true)
     */
    private $duracao;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getServico(): ?string
    {
        return $this->servico;
    }

    public function setServico(string $servico): self
    {
        $this->servico = $servico;

        return $this;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): self
    {
        $this->descricao = $descricao;

        return $this;
    }

    public function getValor(): ?string
    {
        return $this->valor;
    }

    public function setValor(string $valor): self
    {
        $this->valor = $valor;

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

    public function getEmpresa(): ?Empresa
    {
        return $this->empresa;
    }

    public function setEmpresa(Empresa $empresa): self
    {
        $this->empresa = $empresa;

        return $this;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $arquivoFoto
     */
    public function setArquivoFoto(?File $arquivoFoto = null): void
    {
        $this->arquivoFoto = $arquivoFoto;

        if (null !== $arquivoFoto) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getArquivoFoto(): ?File
    {
        return $this->arquivoFoto;
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

    public function getDuracao(): ?string
    {
        if (isset($this->duracao))
            return $this->duracao->format('H:i:s');
        return null;
    }

    public function setDuracao(?string $duracao): self
    {
        $this->duracao = new DateTime($duracao);

        return $this;
    }
}
