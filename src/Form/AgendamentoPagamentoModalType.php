<?php

namespace App\Form;

use App\Entity\AgendamentoPagamento;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgendamentoPagamentoModalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('forma_pagto', ChoiceType::class, [
                'choices' => [
                    'Dinheiro' => 'DINHEIRO',
                    'Débito' => 'DEBITO',
                    'Crédito a Vista' => 'CREDVISTA',
                    'Crédito a Prazo' => 'CREDPRAZO',
                    'Transferência Bancária' => 'TRANSFBANCO',
                    'Carteira Virtual' => 'CREDVIRT'
                ],
                'label' => 'Forma Pagamento'
            ])
            ->add('valor', MoneyType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AgendamentoPagamento::class,
        ]);
    }
}
