<?php

namespace App\Tests\EventSubscriber;

use App\Entity\Pessoa;
use App\Entity\PerfilFuncionario;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DoctrineLicenciamentoFuncionarioTest extends WebTestCase
{
    protected const DATA = [
        [
            'cpf' => '87257864403',
            'nome' => 'Renan Mário dos Santos',
            'telefone' => '92997916068',
            'email' => 'renanmariodossantos@fernandesfilpi.com.br',
            'nomeUsuario' => 'renanmariodossantos',
        ],
        [
            'cpf' => '59040757585',
            'nome' => 'Adriana Josefa Isabela Mendes',
            'telefone' => '92998030380',
            'email' => 'adrianajosefaisabelamendes@spires.com.br',
            'nomeUsuario' => 'adrianajosefaisabela',
        ],
        [
            'cpf' => '74105496964',
            'nome' => 'Bruna Fátima Laura Vieira',
            'telefone' => '21994469166',
            'email' => 'brunafatimalauravieira-73@hitmail.com',
            'nomeUsuario' => 'brunafatimalauravieira',
        ],
        [
            'cpf' => '52799715370',
            'nome' => 'Milena Beatriz Nogueira',
            'telefone' => '68983306783',
            'email' => 'milenabeatriznogueira@yahoo.com.ar',
            'nomeUsuario' => 'milenabeatriznogueira',
        ],
        [
            'cpf' => '54296229907',
            'nome' => 'Aparecida Tatiane Baptista',
            'telefone' => '61984259114',
            'email' => 'aaparecidatatianebaptista@infortec.com',
            'nomeUsuario' => 'aparecidatatianebaptista',
        ],
        [
            'cpf' => '06821842337',
            'nome' => 'Isabelly Vera Bernardes',
            'telefone' => '82982229532',
            'email' => 'isabellyverabernardes@effem.com',
            'nomeUsuario' => 'isabellyverabernardes',
        ],
    ];

    private $em;
    private $pRepo;
    /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client */
    private $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->pRepo = $this->em->getRepository(Pessoa::class);
    }

    // public function testKernelEventFlag(): void
    // {
    //     $pFuncionarioRepo = $this->em->getRepository(PerfilFuncionario::class);
    //     $nfun = count($pFuncionarioRepo->findAll());

    //     for ($i=0; $i <= (10 - $nfun); $i++) {
    //         $this->em->persist(
    //             (new Pessoa())
    //                 ->setCpf(self::DATA[$i]['cpf'])
    //                 ->setNome(self::DATA[$i]['nome'])
    //                 ->setTelefone(self::DATA[$i]['telefone'])
    //         );
    //     }
    //     $this->em->flush();
    //     for ($i=0; $i < (10 - $nfun); $i++) {
    //         $this->em->persist(
    //             (new PerfilFuncionario())
    //                 ->setNomeUsuario(self::DATA[$i]['nomeUsuario'])
    //                 ->setEmail(self::DATA[$i]['email'])
    //                 ->setPassword('$2y$13$.tnRvwM1OvWhKpPAAtYF7evD7rd73lNvl/jStwhTqV63iBh3hjvom') //OLOueTDS
    //                 ->setPessoa(
    //                     $this->pRepo->find(self::DATA[$i]['cpf'])
    //                 )
    //         );
    //     }
    //     $this->em->flush();
    //     $this->em->persist(
    //         (new PerfilFuncionario())
    //             ->setNomeUsuario(self::DATA[$i]['nomeUsuario'])
    //             ->setEmail(self::DATA[$i]['email'])
    //             ->setPassword('$2y$13$.tnRvwM1OvWhKpPAAtYF7evD7rd73lNvl/jStwhTqV63iBh3hjvom') //OLOueTDS
    //             ->setPessoa(
    //                 $this->pRepo->find(self::DATA[$i]['cpf'])
    //             )
    //     );
    //     $this->em->flush();
    //     $novoNfun = count($pFuncionarioRepo->findAll());
    //     $this->assertGreaterThan($nfun, $novoNfun);
    //     $this->assertEquals(10, $novoNfun);
    // }

    public function testClientEventFlag(): void
    {

        $this->client->getCookieJar()->set(new Cookie('cnpj', 38260851000146));
        $userRepository = $this->em->getRepository(PerfilFuncionario::class);
        $testUser = $userRepository->find('isisbrendadaluz');
        $this->client->loginUser($testUser);
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/funcionario');
        $registros = $crawler->filter('.funcionarioRegistros')->count();

        for ($i=0; $i < (10 - $registros); $i++) {
            $crawler = $this->client->request('GET', '/funcionario/new');
            $this->client->submitForm('Salvar', [
                'perfil[pessoa][cpf]' => self::DATA[$i]['cpf'],
                'perfil[pessoa][nome]' => self::DATA[$i]['nome'],
                'perfil[pessoa][telefone]' => self::DATA[$i]['telefone'],
                'perfil[email]' => self::DATA[$i]['email'],
                'perfil[nomeUsuario]' => self::DATA[$i]['nomeUsuario'],
            ]);
        }
        $this->client->request('GET', '/funcionario');
        $crawler = $this->client->getCrawler();
        $this->assertEquals(10, $crawler->filter('.funcionarioRegistros')->count(), "Haviam $registros, $i passadas");
        $registros = 10;

        $novoPerfil = self::DATA[5];
        $crawler = $this->client->request('GET', '/funcionario/new');
        $this->client->submitForm('Salvar', [
            'perfil[pessoa][cpf]' => $novoPerfil['cpf'],
            'perfil[pessoa][nome]' => $novoPerfil['nome'],
            'perfil[pessoa][telefone]' => $novoPerfil['telefone'],
            'perfil[email]' => $novoPerfil['email'],
            'perfil[nomeUsuario]' => $novoPerfil['nomeUsuario'],
        ]);
        $crawler = $this->client->getCrawler();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Lista de funcionários');
        $this->assertSelectorNotExists('div.alert-success');
        $this->assertSelectorExists('div.alert-danger');
        $this->assertEquals(10, $crawler->filter('.funcionarioRegistros')->count(), "Haviam $registros");
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }
}
