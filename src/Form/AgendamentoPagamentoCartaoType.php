<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Luhn;

class AgendamentoPagamentoCartaoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cardNumber', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Luhn()
                ],
                'label' => 'Número Cartão'
            ])
            // ->add('cardExpirationDate', null, [
            //     'required' => false,
            // ])
            ->add('cardExpirationMonth', null, [
                'required' => false,
                'label' => 'Validade (mês)'
            ])
            ->add('cardExpirationYear', null, [
                'required' => false,
                'label' => 'Validade (ano)',
            ])
            ->add('cardHolderName', TextType::class, [
                'required' => false,
                'label' => 'Nome do portador'
            ])
            ->add('cardHolderEmail', EmailType::class, [
                'required' => false,
                'label' => 'Email de contato'
            ])
            ->add('securityCode', null, [
                'required' => false,
                'label' => 'CVV'
            ])
            ->add('issuer', ChoiceType::class, [
                'label' => 'Bandeira'
            ])
            ->add('identificationType', ChoiceType::class, [
                'label' => 'Documento'
            ])
            ->add('identificationNumber', TextType::class, [
                'label' => 'Número Documento'
            ])
            ->add('installments', ChoiceType::class, [
                'label' => 'Parcelas'
            ])
            // ->add('transactionAmount', null, [
            //     'required' => false,
            // ])
            // ->add('description', null, [
            //     'required' => false,
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
