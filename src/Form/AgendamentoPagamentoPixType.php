<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgendamentoPagamentoPixType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [])
            ->add('name', TextType::class, [
                'label' => 'Nome'
            ])
            ->add('identification_type', ChoiceType::class, [
                'label' => 'Documento',
                'choices' => [
                    'CPF' => 'CPF',
                    'CNPJ' => 'CNPJ'
                ]
            ])
            ->add('identification_number', TextType::class, [
                'label' => 'Número documento'
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
