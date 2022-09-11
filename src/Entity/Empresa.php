<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Empresa
 *
 * @ORM\Entity
 * @UniqueEntity("cnpj")
 * @Vich\Uploadable
 */
class Empresa
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false, length=14)
     * @ORM\Id
     * @Assert\Regex(
     *  pattern = "/^\d{14}/",
     *  htmlPattern = "[0-9]{14}",
     *  message = "Digite somente os números do CNPJ"
     * )
     */
    private $cnpj;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=45, nullable=false)
     * @Assert\NotBlank
     */
    private $nomeEmpresa;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=7, nullable=false, options={"default"="#FFFFFF","fixed"=true})
     * @Assert\Regex(
     *      pattern = "/^\#[A-F a-f 0-9]{6}/",
     *      message = "Utilize somente valores hexadecimais precedidos de \#"
     * )
     */
    private $corFundo = '#FFFFFF';

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    private $logo;

        /**
     * @Vich\UploadableField(mapping="configuracao_logo", fileNameProperty="logo")
     *
     * @var File|null
     */
    private $arquivoLogo;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10, nullable=false, options={"default"="Nunca"})
     */
    private $intervaloBloqueio = 'Nunca';

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true, "default"=0})
     */
    private $qtdeBloqueio = '0';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10, nullable=false, options={"default"="Meses"})
     */
    private $intervaloAnalise = 'Meses';

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"default"="1","unsigned"=true})
     */
    private $qtdeAnalise = '1';

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"default"="1000","unsigned"=true})
     */
    private $atrasosTolerados = '1000';

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"default"="1000","unsigned"=true})
     */
    private $cancelamentosTolerados = '1000';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 5,
     *      max = 100,
     *      minMessage = "O endereço é muito curto.",
     *      maxMessage = "O endereço é muito longo.",
     *      allowEmptyString = false
     * )
     */
    private $endereco;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=false)
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "O nome da cidade é muito curto.",
     *      maxMessage = "O nome da cidade é muito longo.",
     *      allowEmptyString = false
     * )
     */
    private $cidade;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=2, nullable=false, options={"fixed"=true})
     */
    private $uf;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true, length=8)
     * @Assert\Regex(
     *  pattern = "/^\d{8}/",
     *  htmlPattern = "[0-9]{8}",
     *  message = "O CEP não foi digitado corretamente. Não utilize pontos e traços."
     * )
     */
    private $cep;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"default"="1","unsigned"=true})
     */
    private $qtdeLicencas = '1';

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */

    private $corTexto;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $corLabel;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $corBoxes;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $corInput;

    /**
     * Determine o período de funcionamento da empresa
     *
     * @var Collection|EmpresaTurnoTrabalho[]
     *
     * @ORM\OneToMany(targetEntity=EmpresaTurnoTrabalho::class, mappedBy="empresa", cascade={"persist"}, orphanRemoval=true)
     */
    private $horarioTrabalho;

    /**
     * Trabalhadores e seus privilegios
     *
     * @var Collection|FuncionarioLocalTrabalho[]
     *
     * @ORM\OneToMany(targetEntity=FuncionarioLocalTrabalho::class, mappedBy="cnpj", cascade={"persist"})
     */
    private $funcionarios;

    /**
     * Serviços vinculados a esta empresa
     *
     * @ORM\OneToMany(targetEntity=Servico::class, mappedBy="empresa")
     * @var Collection|Servico[]
     */
    private $servicos;

    /**
     * @ORM\OneToMany(targetEntity=EmpresaProcessadorPagamento::class, mappedBy="empresa", orphanRemoval=true)
     */
    private $empresaProcessadorPagamentos;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cpf = new ArrayCollection();
        $this->horarioTrabalho = new ArrayCollection();
        $this->funcionarios = new ArrayCollection();
        $this->servicos = new ArrayCollection();
        $this->empresaProcessadorPagamentos = new ArrayCollection();
    }

    public function getCnpj(): ?string
    {
        return $this->cnpj;
    }

    public function setCnpj(string $cnpj): self
    {
        if(!isset($this->cnpj))
        {
            $this->cnpj=$cnpj;
        }
        return $this;
    }

    public function getNomeEmpresa(): ?string
    {
        return $this->nomeEmpresa;
    }

    public function setNomeEmpresa(string $nomeEmpresa): self
    {
        $this->nomeEmpresa = $nomeEmpresa;

        return $this;
    }

    public function getCorFundo(): ?string
    {
        return $this->corFundo;
    }

    public function setCorFundo(string $corFundo): self
    {
        $this->corFundo = $corFundo;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getIntervaloBloqueio(): ?string
    {
        return $this->intervaloBloqueio;
    }

    public function setIntervaloBloqueio(string $intervaloBloqueio): self
    {
        $this->intervaloBloqueio = $intervaloBloqueio;

        return $this;
    }

    public function getQtdeBloqueio(): ?int
    {
        return $this->qtdeBloqueio;
    }

    public function setQtdeBloqueio(int $qtdeBloqueio): self
    {
        $this->qtdeBloqueio = $qtdeBloqueio;

        return $this;
    }

    public function getIntervaloAnalise(): ?string
    {
        return $this->intervaloAnalise;
    }

    public function setIntervaloAnalise(string $intervaloAnalise): self
    {
        $this->intervaloAnalise = $intervaloAnalise;

        return $this;
    }

    public function getQtdeAnalise(): ?int
    {
        return $this->qtdeAnalise;
    }

    public function setQtdeAnalise(int $qtdeAnalise): self
    {
        $this->qtdeAnalise = $qtdeAnalise;

        return $this;
    }

    public function getAtrasosTolerados(): ?int
    {
        return $this->atrasosTolerados;
    }

    public function setAtrasosTolerados(int $atrasosTolerados): self
    {
        $this->atrasosTolerados = $atrasosTolerados;

        return $this;
    }

    public function getCancelamentosTolerados(): ?int
    {
        return $this->cancelamentosTolerados;
    }

    public function setCancelamentosTolerados(int $cancelamentosTolerados): self
    {
        $this->cancelamentosTolerados = $cancelamentosTolerados;

        return $this;
    }

    public function getEndereco(): ?string
    {
        return $this->endereco;
    }

    public function setEndereco(string $endereco): self
    {
        $this->endereco = $endereco;

        return $this;
    }

    public function getCidade(): ?string
    {
        return $this->cidade;
    }

    public function setCidade(string $cidade): self
    {
        $this->cidade = $cidade;

        return $this;
    }

    public function getUf(): ?string
    {
        return $this->uf;
    }

    public function setUf(string $uf): self
    {
        $this->uf = $uf;

        return $this;
    }

    public function getCep(): ?string
    {
        return $this->cep;
    }

    public function setCep(?string $cep): self
    {
        $this->cep = $cep;

        return $this;
    }

    public function getQtdeLicencas(): ?int
    {
        return $this->qtdeLicencas;
    }

    public function setQtdeLicencas(int $qtdeLicencas): self
    {
        $this->qtdeLicencas = $qtdeLicencas;

        return $this;
    }

    public function getCorTexto() : ?string
    {
        return $this->corTexto;
    }

    public function setCorTexto(?string $corTexto) :self
    {
        $this->corTexto = $corTexto;
        return $this;
    }

    public function getCorInput() : ?string
    {
        return $this->corInput;
    }

    public function setCorInput(?string $corInput) :self
    {
        $this->corInput = $corInput;
        return $this;
    }


    public function getCorBoxes() : ?string
    {
        return $this->corBoxes;
    }

    public function setCorBoxes(?string $corBoxes) :self
    {
        $this->corBoxes = $corBoxes;
        return $this;
    }


    public function getCorLabel() : ?string
    {
        return $this->corLabel;
    }

    public function setCorLabel(?string $corLabel) :self
    {
        $this->corLabel = $corLabel;
        return $this;
    }


    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $arquivoLogo
     */
    public function setArquivoLogo(?File $arquivoLogo = null): void
    {
        $this->arquivoLogo = $arquivoLogo;

        if (null !== $arquivoLogo) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getArquivoLogo(): ?File
    {
        return $this->arquivoLogo;
    }


    // /**
    //  * @return Collection|Cliente[]
    //  */
    // public function getCpf(): Collection
    // {
    //     return $this->cpf;
    // }

    // public function addCpf(Cliente $cpf): self
    // {
    //     if (!$this->cpf->contains($cpf)) {
    //         $this->cpf[] = $cpf;
    //     }

    //     return $this;
    // }

    // public function removeCpf(Cliente $cpf): self
    // {
    //     if ($this->cpf->contains($cpf)) {
    //         $this->cpf->removeElement($cpf);
    //     }

    //     return $this;
    // }

    /**
     * @return Collection|PerfilFuncionario[]
     */
    public function getCpfFuncionario(): Collection
    {
        return $this->cpfFuncionario;
    }

    public function addCpfFuncionario(PerfilFuncionario $cpfFuncionario): self
    {
        if (!$this->cpfFuncionario->contains($cpfFuncionario)) {
            $this->cpfFuncionario[] = $cpfFuncionario;
        }

        return $this;
    }

    public function removeCpfFuncionario(PerfilFuncionario $cpfFuncionario): self
    {
        if ($this->cpfFuncionario->contains($cpfFuncionario)) {
            $this->cpfFuncionario->removeElement($cpfFuncionario);
        }

        return $this;
    }

    /**
     * Undocumented function
     *
     * @return Collection|EmpresaTurnoTrabalho[]
     */
    public function getHorarioTrabalho(): Collection
    {
        return  $this->horarioTrabalho;
    }

    public function addHorarioTrabalho(EmpresaTurnoTrabalho $horarioTrabalho) : self {
        if (!$this->horarioTrabalho->contains($horarioTrabalho)){
            $this->horarioTrabalho[] = $horarioTrabalho;
            $horarioTrabalho->setEmpresa($this);
        }
        return $this;
    }

    public function removeHorarioTrabalho(EmpresaTurnoTrabalho $horarioTrabalho) : self {
        if($this->horarioTrabalho->contains($horarioTrabalho)){
            $this->horarioTrabalho->removeElement($horarioTrabalho);
            $horarioTrabalho->setEmpresa(null);
            $horarioTrabalho->setDiaSemana(null);
        }
        return $this;
    }

    /**
     * Undocumented function
     *
     * @return Collection|FuncionarioLocalTrabalho[]
     */
    public function getFuncionarios(): Collection
    {
        return  $this->funcionarios;
    }

    public function addFuncionario(FuncionarioLocalTrabalho $funcionario) : self {
        if (!$this->funcionarios->contains($funcionario)){
            $this->funcionarios[] = $funcionario;
            $funcionario->setCnpj($this);
        }
        return $this;
    }

    public function removeFuncionario(FuncionarioLocalTrabalho $funcionario) : self {
        if($this->funcionarios->contains($funcionario)){
            $this->funcionarios->removeElement($funcionario);
        }
        return $this;
    }

    /**
     * Undocumented function
     *
     * @return Collection|Servico[]
     */
    public function getServico(): Collection
    {
        return  $this->servicos;
    }

    public function addServico(Servico $servico) : self {
        if (!$this->servicos->contains($servico)){
            $this->servicos[] = $servico;
            $servico->setEmpresa($this);
        }
        return $this;
    }

    public function removeServico(Servico $servico) : self {
        if($this->servicos->contains($servico)){
            $this->servicos->removeElement($servico);
        }
        return $this;
    }

    /**
     * @return Collection|EmpresaProcessadorPagamento[]
     */
    public function getEmpresaProcessadorPagamentos(): Collection
    {
        return $this->empresaProcessadorPagamentos;
    }

    public function addEmpresaProcessadorPagamento(EmpresaProcessadorPagamento $empresaProcessadorPagamento): self
    {
        if (!$this->empresaProcessadorPagamentos->contains($empresaProcessadorPagamento)) {
            $this->empresaProcessadorPagamentos[] = $empresaProcessadorPagamento;
            $empresaProcessadorPagamento->setEmpresa($this);
        }

        return $this;
    }

    public function removeEmpresaProcessadorPagamento(EmpresaProcessadorPagamento $empresaProcessadorPagamento): self
    {
        if ($this->empresaProcessadorPagamentos->removeElement($empresaProcessadorPagamento)) {
            // set the owning side to null (unless already changed)
            if ($empresaProcessadorPagamento->getEmpresa() === $this) {
                $empresaProcessadorPagamento->setEmpresa(null);
            }
        }

        return $this;
    }
}
