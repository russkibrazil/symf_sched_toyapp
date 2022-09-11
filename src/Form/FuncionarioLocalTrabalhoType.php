<?php

namespace App\Form;

use App\Entity\Empresa;
use App\Entity\FuncionarioLocalTrabalho;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FuncionarioLocalTrabalhoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $cnpj_options = [
            'label' => 'Empresa',
            'class' => Empresa::class,
            'choice_label' => 'nome_empresa',
            'disabled' => true
        ];
        if ($options['operacao'] == 'novo' && $options['currentUserHasRole_Proprietario'])
        {
            $cnpj_options['disabled'] = false;
        }
        // TODO: Validar Checkboxes - Ao menos um selecionado
        $builder
            ->add('cnpj', EntityType::class, $cnpj_options)
            ->add('ativo', CheckboxType::class)
            ->add('privilegioCaixa', CheckboxType::class, [
                'label' => 'Caixa',
                'required' => false,
                'mapped' => false,
                'disabled' => $options['targetUserHasRole_Proprietario'],
            ])
            ->add('privilegioPrestador', CheckboxType::class, [
                'label' => 'Prestador',
                'required' => false,
                'mapped' => false,
            ])
            ->add('privilegioRecepcao', CheckboxType::class, [
                'label' => 'Recepção',
                'required' => false,
                'mapped' => false,
                'disabled' => $options['targetUserHasRole_Proprietario'],
            ])
            ->add('privilegioAdmin', CheckboxType::class, [
                'label' => 'Administrador',
                'required' => false,
                'mapped' => false,
                'disabled' => $options['targetUserHasRole_Proprietario']
            ])
            ->add('salario', MoneyType::class, [
                'currency' => 'BRL',
                'required' => false,
                'label' => 'Salário'
            ])
            ->add('comissao', PercentType::class, [
                'required' => false,
                'label' => 'Comissão'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => FuncionarioLocalTrabalho::class,
                'operacao' => 'editar',
                'currentUserHasRole_Proprietario' => false,
                'targetUserHasRole_Proprietario' => false,
            ])
            ->setAllowedTypes('operacao', 'string')
            ->setAllowedTypes('currentUserHasRole_Proprietario', 'bool')
            ->setAllowedTypes('targetUserHasRole_Proprietario', 'bool')
            ->setAllowedValues('operacao', ['editar', 'novo'])
        ;
    }
}
