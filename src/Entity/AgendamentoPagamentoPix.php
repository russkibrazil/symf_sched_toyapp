<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class AgendamentoPagamentoPix
{
    /**
     * Undocumented variable
     * @Assert\NotNull
     * @Assert\Length(min = 12, minMessage = "Digite o nome completo")
     * @var string
     */
    private $name;

    /**
     * Undocumented variable
     * @Assert\Email
     * @var string
     */
    private $email;

    /**
     * Undocumented variable
     * @Assert\Choice({"CPF", "CNPJ"})
     * @var string
     */
    private $identification_type;

    /**
     * Undocumented variable
     * @Assert\NotNull
     * @var string
     */
    private $identification_number;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getIdentificationType(): string
    {
        return $this->identification_type;
    }

    public function setIdentificationType(string $type): self
    {
        $this->identification_type = $type;
        return $this;
    }

    public function getIdentificationNumber(): string
    {
        return $this->identification_number;
    }

    public function setIdentificationNumber(string $id_number): self
    {
        $this->identification_number = $id_number;
        return $this;
    }
}