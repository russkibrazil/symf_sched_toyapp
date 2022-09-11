<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * FuncionarioTurnoTrabalho
 *
 * @ORM\Entity
 * @UniqueEntity(fields={"cnpj", "cpfFuncionario", "diaSemana", "horaInicio"}, message="Este turno já está cadastrado")
 */
class FuncionarioTurnoTrabalho
{
    /**
     * @var Empresa
     *
     * @ORM\ManyToOne(targetEntity=Empresa::class)
     * @ORM\JoinColumn(referencedColumnName="cnpj", nullable=false)
     * @ORM\Id
     */
    private $cnpj;

    /**
     * @var PerfilFuncionario
     *
     * @ORM\ManyToOne(targetEntity=PerfilFuncionario::class, inversedBy="funcionarioTurnoTrabalho")
     * @ORM\JoinColumn(referencedColumnName="nome_usuario", nullable=false)
     * @ORM\Id
     */
    private $cpfFuncionario;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @Assert\Range(
     *  min = 1,
     *  max = 8,
     *  notInRangeMessage = "Dia da semana inválido. É experado valor entre {{ min }} e {{ max }}")
     */
    private $diaSemana;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="time", nullable=false)
     * @Assert\NotEqualTo(propertyPath="horaFim")
     */
    private $horaInicio;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="time", nullable=false)
     */
    private $horaFim;


    public function getDiaSemana(): ?int
    {
        return $this->diaSemana;
    }

    public function setDiaSemana($dia_semana): self
    {
        if (!isset($this->diaSemana))
        {
            $this->diaSemana = $dia_semana;
        }

        return $this;
    }

    public function getHoraInicio(): ?\DateTimeInterface
    {
        return $this->horaInicio;
    }

    public function setHoraInicio(DateTime $horaInicio): self
    {
        /*$tempt = explode(':', $horaInicio, 4);
        $dtobj = new DateTime();
        $dtobj->setTime($tempt[0], $tempt[1], $tempt[2]);*/

        $this->horaInicio = $horaInicio;

        return $this;
    }

    public function getHoraFim(): ?\DateTimeInterface
    {
        return $this->horaFim;
    }

    public function setHoraFim(DateTime $horaFim): self
    {
        /*$tempt = explode(':', $horaFim, 4);
        $dtobj = new DateTime();
        $dtobj->setTime($tempt[0], $tempt[1], $tempt[2]);*/

        $this->horaFim = $horaFim;

        return $this;
    }

    public function getCnpj(): ?Empresa
    {
        return $this->cnpj;
    }

    public function setCnpj(?Empresa $cnpj): self
    {
        $this->cnpj = $cnpj;

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


}
