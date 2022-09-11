<?php

namespace App\DataFixtures;

use App\Entity\Agendamento;
use App\Entity\AgendamentoPagamento;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AgendamentoPagamentoFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /**
         * @var \App\Repository\AgendamentoRepository $aRepo
         * @var \App\Entity\Agendamento $ag1
         * @var \App\Entity\Agendamento $ag2
         */
        $aRepo = $manager->getRepository(Agendamento::class);
        $ag1 = $aRepo->findOneBy([
            'pagamentoPresencial' => true,
            'pagamentoPendente' => false
        ]);
        $ag2 = $aRepo->findOneBy([
            'pagamentoPresencial' => false,
            'pagamentoPendente' => false
        ]);
        // Enquanto os agendamentos teste tiverem o mesmo serviço, isto é válido
        $valorAgendamento = $ag1->getServicos()->first()->getServico()->getValor();

        $pagamento1 = (new AgendamentoPagamento())
            ->setAgendamento($ag1)
            ->setCapturado(true)
            ->setData(new DateTime())
            ->setFormaPagto($ag1->getFormaPagto())
            ->setUltimaModificacao(new DateTime())
            ->setValor($valorAgendamento)
            ->setStatusAtual('accredited')
        ;
        $pagamento2 = (new AgendamentoPagamento())
            ->setAgendamento($ag2)
            ->setCapturado(true)
            ->setData(new DateTime())
            ->setFormaPagto($ag2->getFormaPagto())
            ->setUltimaModificacao(new DateTime())
            ->setValor($valorAgendamento)
            ->setStatusAtual('accredited')
        ;
        $manager->persist($pagamento1);
        $manager->persist($pagamento2);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            AgendamentoFixture::class
        ];
    }
}
