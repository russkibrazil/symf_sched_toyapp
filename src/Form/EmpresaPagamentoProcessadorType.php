<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use App\Entity\EmpresaProcessadorPagamento;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\EmpresaPagamentoProcessadorFaixaValorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class EmpresaPagamentoProcessadorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('processador', ChoiceType::class, [
                'label' => 'Serviço',
                'disabled' => $options['operacao'] == 'novo' ? false : true,
                'choices' => [
                    'Mercado Pago' => 'MERPAGO'
                ]
            ])
            ->add('maxParcelasCartao', IntegerType::class, [
                'label' => 'Parcelamento máximo',
                'attr' => [
                    'min' => 1,
                ],
                'empty_data' => '12',
            ])
            ->add('politicaParcelamento', CollectionType::class, [
                'entry_type' => EmpresaPagamentoProcessadorFaixaValorType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ])
            ->add('tipoChavePix', ChoiceType::class, [
                'label' => 'Tipo chave',
                'mapped' => false,
                'choices' => [
                    'CPF/CNPJ' => 'documento',
                    'Telefone' => 'telefone',
                    'Email' => 'email'
                ]
            ])
            ->add('pix', TextType::class, [
                'label' => 'Chave',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EmpresaProcessadorPagamento::class,
            'operacao' => 'editar'
        ]);
        $resolver->setAllowedTypes('operacao', 'string');
        $resolver->setAllowedValues('operacao', ['editar', 'novo']);
    }
}
