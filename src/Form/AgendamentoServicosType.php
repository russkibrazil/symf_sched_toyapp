<?php

namespace App\Form;

use App\Entity\AgendamentoServicos;
use App\Entity\Servico;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManagerInterface;

class AgendamentoServicosType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    private function getElements($emp)
    {
        $svc = $this->entityManager->getRepository(Servico::class)
            ->findBy(['empresa' => $emp]);
        $servicos = [];
        foreach ($svc as $r){
            $servicos[] = $r->getId();
        }
        return $svc;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('servico', ChoiceType::class, [
                'choices' => $this->getElements($options['empresa']),
                'choice_value' => 'id',
                'choice_label' => function (?Servico $s){
                    return $s ? $s->getServico() : '';
                },
                'label' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AgendamentoServicos::class,
            'empresa' => '0'
        ]);
        $resolver->setAllowedTypes('empresa', 'string');
    }
}
