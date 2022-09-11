<?php

namespace App\Form;

use App\Entity\Perfil;
use App\Form\PessoaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class PerfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomeUsuario', TextType::class, [
                'label' => 'Nome de usuário',
                'disabled' => $options['operacao'] == 'edit' ? true : false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Digite um nome de usuário'
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'É necessário ter ao menos {{ limit }} caracteres',
                        'max' => 25,
                        'maxMessage' => 'É permitido no máximo {{ limit }} caracteres',
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail'
            ])
            ->add('pessoa', PessoaType::class, ['operacao' => $options['operacao']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Perfil::class,
            'operacao' => 'edit'
        ]);
        $resolver->setAllowedTypes('operacao', 'string');
        $resolver->setAllowedValues('operacao', ['new', 'edit']);
    }
}
