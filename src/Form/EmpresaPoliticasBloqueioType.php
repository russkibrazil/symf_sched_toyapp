<?php

namespace App\Form;

use App\Entity\Empresa;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmpresaPoliticasBloqueioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('intervaloBloqueio', ChoiceType::class, [
                'choices' => [
                    'Dias' => 'DIA',
                    'Meses' => 'MES',
                    'Sem Bloqueio' => 'NUNCA'
                ],
                'label' => 'Bloquear usuários',
                'help' => 'Permite selecionar o período de bloqueio de clientes, devido a mau uso.'

            ])
            ->add('qtdeBloqueio', RangeType::class, [
                'attr' => [
                    'min' => 1,
                    'max' => 30,
                    'step' => 1
                ]
            ])
            ->add('intervaloAnalise', ChoiceType::class, [
                'choices' => [
                    'Dias' => 'DIA',
                    'Semanas' => 'SEMANA',
                    'Meses' => 'MES'
                ],
                'label' => 'Período de observação',
                'help' => 'Determina por quanto tempo o sistema mantém e contabiliza os problemas de conduta dos clientes.'
            ])
            ->add('qtdeAnalise', RangeType::class, [
                'attr' => [
                    'min' => 1,
                    'max' => 30,
                    'step' => 1
                ]
            ])
            ->add('atrasosTolerados', RangeType::class, [
                'attr' => [
                    'min' => 0,
                    'max' => 30,
                    'step' => 1
                ],
                'label' => 'Atrasos permitidos',
                'help' => 'Atrasos tolerados por cliente. Respeita o período de observação.'
            ])
            ->add('cancelamentosTolerados', RangeType::class, [
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                    'step' => 5
                ],
                'label' => 'Cancelamentos permitidos',
                'help' => 'Cancelamentos de agendamentos tolerados por cliente. Respeita o período de observação.'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Empresa::class,
        ]);
    }
}
