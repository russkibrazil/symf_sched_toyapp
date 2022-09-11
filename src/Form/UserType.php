<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cpf', TextType::class, [
                'label' => 'CPF'
            ])
            ->add('nome', TextType::class)
            ->add('telefone', TelType::class)
            ->add('nascimento', BirthdayType::class, [
                'label' => 'Data nascimento'
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail'
            ])
            ->add('endereco', TextType::class, [
                'required' => false,
                'label' => 'Endereço'
            ])
            ->add('cidade', TextType::class, [
                'required' => false,
            ])
            ->add('uf', ChoiceType::class, [
                'choices' => [
                    'AC' => 'AC',
                    'AL' => 'AL',
                    'AM' => 'AM',
                    'AP' => 'AP',
                    'BA' => 'BA',
                    'CE' => 'CE',
                    'DF' => 'DF',
                    'ES' => 'ES',
                    'GO' => 'GO',
                    'MA' => 'MA',
                    'MG' => 'MG',
                    'MS' => 'MS',
                    'MT' => 'MT',
                    'PA' => 'PA',
                    'PB' => 'PB',
                    'PE' => 'PE',
                    'PI' => 'PI',
                    'PR' => 'PR',
                    'RJ' => 'RJ',
                    'RN' => 'RN',
                    'RO' => 'RO',
                    'RR' => 'RR',
                    'RS' => 'RS',
                    'SC' => 'SC',
                    'SE' => 'SE',
                    'SP' => 'SP',
                    'TO' => 'TO'
                ],
                'label' => 'Estado'
            ])
            ->add('cep', TextType::class, [
                'required' => false,
                'label' => 'CEP'
            ])
            ->add('ctps', FuncionarioType::class)
            ->add('fpagamentoFav', ChoiceType::class, [
                'choices' => [],
                'label' => 'Pagamento Favorito',
                'help' => 'Defina qual o seu método de pagamento favorito. Seus agendamentos lembrarão dessa opção.'
            ])
            ->add('arquivoFoto', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => true,
                'asset_helper' => true
            ])
            ->add('funcionarioLocalTrabalho', CollectionType::class, [
                'entry_type' => FuncionarioLocalTrabalhoType::class,
                'entry_options' => ['label' => false],
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => false
            ])
            ->add('funcionarioTurnoTrabalho', CollectionType::class, [
                'entry_type' => FuncionarioTurnoTrabalhoType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ])
        ;
        if ($options['operacao'] == 'novo')
        {
            $builder->add('password', PasswordType::class, [
                'label' => 'Senha',
                'help' => 'Defina a senha para entrar que misture pelo menos letras e números.'
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'operacao' => 'editar'
        ]);
        $resolver->setAllowedTypes('operacao','string');
        $resolver->setAllowedValues('operacao', ['editar', 'novo']);
    }
}
