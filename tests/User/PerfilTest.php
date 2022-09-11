<?php

namespace App\Tests\User;

use App\Entity\PerfilCliente;
use App\Entity\PerfilFuncionario;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PerfilTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Repositório da classe User
     * @var \App\Repository\PerfilClienteRepository
     */
    private $clienteRepository;

    /**
     * PerfilFuncionario Repository
     *
     * @var \App\Repository\PerfilFuncionarioRepository
     */
    private $funcionarioRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

            $this->clienteRepository = $this->entityManager->getRepository(PerfilCliente::class);
            $this->funcionarioRepository = $this->entityManager->getRepository(PerfilFuncionario::class);
    }

    public function testLoadRoles(): void
    {
        /**
         * @var \App\Entity\User $cliente
         */
        $cliente = $this->clienteRepository->find('victorpietroviana');
        /**
         * @var \App\Entity\User $func
         */
        $func = $this->funcionarioRepository->find('isisbrendadaluz');

        $this->assertContainsEquals('ROLE_USER', $cliente->getRoles(), 'O cliente testado tem mais privilégios que o necessário');
        $this->assertNotContains('ROLE_USER', $func->getRoles(), 'Os privilégios do funcionário contém privilégios de cliente.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
