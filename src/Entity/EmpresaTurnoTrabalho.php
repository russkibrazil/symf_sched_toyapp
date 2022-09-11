<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * EmpresaTurnoTrabalho
 *
 * @ORM\Entity
 * @UniqueEntity(fields={"cnpj", "diaSemana", "horaInicio"}, message="Este turno já está cadastrado")
 */
class EmpresaTurnoTrabalho
{
    /**
     * @var Empresa
     *
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="horarioTrabalho")
     * @ORM\JoinColumn(referencedColumnName="cnpj", nullable=false)
     * @ORM\Id
     */
    private $empresa;

    /**
     * @var int
     * @see https://www.php.net/manual/en/datetime.format.php
     *
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @Assert\Range(
     *  min = 0,
     *  max = 6,
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


    public function getDiaSemana(): ?string
    {
        return $this->diaSemana;
    }

    public function setDiaSemana($diaSemana): self
    {
        if (!isset($this->diaSemana))
        {
            $this->diaSemana = $diaSemana;
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

    public function getEmpresa(): ?Empresa
    {
        return $this->empresa;
    }

    public function setEmpresa(?Empresa $empresa): self
    {
        $this->empresa = $empresa;

        return $this;
    }


}
