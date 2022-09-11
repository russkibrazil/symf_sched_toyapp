<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmpresaPagamentoProcessadorFaixaValorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startValue', MoneyType::class, [
              'currency' => 'BRL',
              'divisor' => 100,
            ])
            ->add('endValue', MoneyType::class, [
              'currency' => 'BRL',
              'divisor' => 100,
            ])
            ->add('maxInstallments', IntegerType::class, [
              'attr' => [
                'min' => 1,
              ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
