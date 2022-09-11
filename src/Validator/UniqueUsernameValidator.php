<?php

namespace App\Validator;

use App\Entity\PerfilCliente;
use App\Entity\PerfilFuncionario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueUsernameValidator extends ConstraintValidator
{
    private $em;
    public function __construct(EntityManagerInterface $interface)
    {
        $this->em = $interface;
    }
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\UniqueUsername */

        if (null === $value || '' === $value) {
            return;
        }

        $pc = $this->em->getRepository(PerfilCliente::class)->find($value);
        $pf = $this->em->getRepository(PerfilFuncionario::class)->find($value);

        if (null !== $pc || null !== $pf)
        {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->atPath('nomeUsuario')
                ->addViolation();
        }
    }
}
