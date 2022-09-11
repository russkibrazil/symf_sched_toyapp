<?php

namespace App\Tests\User;

use App\Entity\PerfilCliente;
use App\Entity\PerfilFuncionario;
use App\Entity\Pessoa;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PessoaTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var \App\Repository\PessoaRepository
     */
    private $pessoaRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
        ;

        $this->pessoaRepository = $this->entityManager->getRepository(Pessoa::class);
    }

    /**
     * Undocumented function
     * @dataProvider usersProvider
     * @param string $fqrcn
     * @param string $nomeUsuario
     * @return void
     */
    public function testDestroyEverythingAboutMe(string $fqrcn, string $nomeUsuario): void
    {
        $selectedRepo = $this->entityManager->getRepository($fqrcn);
        $user = $selectedRepo->find($nomeUsuario);
        $this->assertNotNull($user);
        $this->pessoaRepository->apagarUsuario($user);
        $this->entityManager->detach($user);
        $newUserCopy = $selectedRepo->find($nomeUsuario);
        $this->assertNull($newUserCopy, 'Falha ao apagar o usuário');
    }

    public function usersProvider(): array
    {
        return [
            'eraseNonAdministrativeUser' => [PerfilFuncionario::class, 'agathaevelynmendes'],
            'eraseAdministrativeUser' => [PerfilFuncionario::class, 'isisbrendadaluz'],
            'eraseClientUser' => [PerfilCliente::class, 'laviniacristianemalusilva'],
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
