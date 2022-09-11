<?php

namespace App\Controller;

use App\Entity\Pessoa;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;

class PessoaController extends AbstractController
{
    private $doctrine;

    public function __construct(EntityManagerInterface $entitymanager)
    {
        $this->doctrine = $entitymanager;
    }
    /**
     * @Route("/pessoa", name="pessoa")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(Request $request): JsonResponse
    {
        $pRepo = $this->doctrine->getRepository(Pessoa::class);
        $args = $request->query;
        if (!$args->has('cpf'))
        {
            return new JsonResponse(null, 400);
        }

        /**
         * @var \App\Entity\Pessoa|null
         */
        $res = $pRepo->find($args->get('cpf'));
        if ($res === null)
        {
            return new JsonResponse(null, 404);
        }

        return new JsonResponse([
            'nome' => $res->getNome(),
            'telefone' => $res->getTelefone(),
            'endereco' => $res->getEndereco()
        ]);
    }
}
