<?php

namespace App\Form;

use App\Form\PessoaType;
use App\Entity\PerfilCliente;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use MeteoConcept\HCaptchaBundle\Form\HCaptchaType;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomeUsuario', TextType::class, [
                'label' => 'Nome de usuário',
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
            ->add('pessoa', PessoaType::class, ['operacao' => 'new'])

            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'Eu concordo com a <a href="#" target="_blank">política de privacidade</a> e com os <a href="#" target="_blank" rel="noopener noreferrer">termos de uso</a> do serviço.',
                // 'attr' => [
                //     'class' => 'custom-control-input'
                // ],
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'É necessário concordar com os termos antes de continuar.',
                    ]),
                ],
                // 'label_attr' => [
                //     'class' => 'custom-control-label'
                // ]
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Senha',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Insira uma senha',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Sua senha deve ter no mínimo {{ limit }} caracteres',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('captcha', HCaptchaType::class, [
                'label' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PerfilCliente::class,
        ]);
    }
}
