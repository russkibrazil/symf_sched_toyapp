<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserApagarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Digite o e-mail desta conta',
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'O email não pode estar vazio'
                    ]),
                    new EqualTo([
                        'value' => $options['email'],
                        'message' => 'Este não parece ser seu email atual'
                    ])
                ]
            ])
            ->add('senha', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'As senhas devem ser iguais.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Insira a senha'],
                'second_options' => ['label' => 'Confirme a senha'],
                'mapped' => false,
                'constraints' => [
                    new UserPassword([
                        'message' => 'Esta não parece ser sua senha atual.'
                    ])
                ]
            ])
            ->add('ciente', CheckboxType::class, [
                'label' => 'Estou ciente de que meus dados serão apagados irreversivelmente.'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'email' => '',
        ]);
        $resolver->setAllowedTypes('email', 'string');
    }
}
