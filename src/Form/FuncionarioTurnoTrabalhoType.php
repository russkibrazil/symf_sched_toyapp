<?php

namespace App\Form;

use App\Entity\FuncionarioTurnoTrabalho;
use App\Entity\Empresa;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FuncionarioTurnoTrabalhoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('diaSemana', ChoiceType::class, [
                'choices' => [
                    'Domingo' => 1,
                    'Segunda' => 2,
                    'Terça' => 3,
                    'Quarta' => 4,
                    'Quinta' => 5,
                    'Sexta' => 6,
                    'Sábado' => 7,
                    'Feriado' => 8
                ],
                'label' => 'Dia da semana'
            ])
            ->add('horaInicio', TimeType::class, [
                'label' => 'Início',
                'help' => 'Selecione o início do turno.'
            ])
            ->add('horaFim', TimeType::class, [
                'label' => 'Fim',
                'help' => 'Selecione o fim do turno.'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FuncionarioTurnoTrabalho::class,
            'operacao' => 'editar'
        ]);
        $resolver->setAllowedTypes('operacao', 'string');
        $resolver->setAllowedValues('operacao', ['editar', 'novo']);
    }
}
