<?php

namespace App\Form;

use App\Entity\Pessoa;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PessoaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cpf', TextType::class, [
                'label' => 'CPF',
                'disabled' => $options['operacao'] == 'edit' ? true : false
            ])
            ->add('nome', TextType::class)
            ->add('telefone', TelType::class)
            ->add('endereco', TextType::class, [
                'required' => false,
                'label' => 'Endereço'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pessoa::class,
            'operacao' => 'edit'
        ]);
        $resolver->setAllowedTypes('operacao', 'string');
        $resolver->setAllowedValues('operacao', ['new', 'edit']);
    }
}
