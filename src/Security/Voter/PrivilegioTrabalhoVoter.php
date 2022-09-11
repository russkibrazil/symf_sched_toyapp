<?php

namespace App\Security\Voter;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PrivilegioTrabalhoVoter extends Voter
{
    /**
     * Undocumented variable
     *
     * @var \Doctrine\ORM\EntityManager $em
     */
    private $em;

    public const ROLE_CAIXA = 'ROLE_CAIXA';
    public const ROLE_RECEPCAO = 'ROLE_RECEPCAO';
    public const ROLE_PRESTADOR = 'ROLE_PRESTADOR';
    public const GROUP_FUNCIONARIO = [self::ROLE_CAIXA, self::ROLE_RECEPCAO, self::ROLE_PRESTADOR];
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_OWNER = 'ROLE_PROPRIETARIO';

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    protected function supports(string $attribute, $subject): bool
    {
        //todo _subject_ tem que ser uma representação da empresa a ser testada
        return in_array($attribute, [self::ROLE_CAIXA, self::ROLE_RECEPCAO, self::ROLE_PRESTADOR, self::ROLE_ADMIN, self::ROLE_OWNER]) && is_string($subject);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }
        /**
         * @var \App\Entity\User $perfil
         */
        $perfil = $this->em->getRepository(User::class)->findOneBy(['email' => $user->getUsername()]);

        $trabalho = array_filter($perfil->getFuncionarioLocalTrabalho()->toArray(), function ($el) use ($subject){
            return $el->getCnpj()->getCnpj() == $subject;
        });
        /**
         * @var \App\Entity\FuncionarioTurnoTrabalho[] $escala
         */
        $escala = array_filter($perfil->getFuncionarioTurnoTrabalho()->toArray(), function ($el) use ($subject){
            return $el->getCnpj()->getCnpj() == $subject;
        });

        if (count($trabalho) == 0 || count($escala) == 0){
            return false;
        }
        /**
         * @var \App\Entity\FuncionarioLocalTrabalho $ficha
         */
        $ficha = $trabalho[0];
        if (!(in_array($attribute, $ficha->getPrivilegios()) || $ficha->getAtivo())){
            return false;
        }

        $agora = new \DateTime();
        $diaSemana = $agora->format('w');
        $diaMatches = array_filter($escala, function ($el) use ($diaSemana) {
            return $el->getDiaSemana() == $diaSemana;
        });
        if (count($diaMatches) == 0){
            return false;
        }
        $dia = $diaMatches[0];
        $tsAgora = $agora->getTimestamp();
        $tsHoje = date_create_from_format('Y-m-d', $agora->format('Y-m-d'))->getTimestamp();
        if ($tsAgora < ($tsHoje + $dia->getHoraInicio()->getTimestamp()) || $tsAgora > ($tsHoje + $dia->getHoraFim()->getTimestamp()))
        {
            return false;
        }
        return true;
    }
}


// use Symfony\Component\Security\Core\Security;

// class PostVoter extends Voter
// {

//     private $security;

//     public function __construct(Security $security)
//     {
//         $this->security = $security;
//     }

//     protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
//     {
//         if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
//             return true;
//         }
//     }
// }