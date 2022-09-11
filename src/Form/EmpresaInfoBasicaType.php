<?php

namespace App\Form;

use App\Entity\Empresa;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class EmpresaInfoBasicaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nomeEmpresa', TextType::class, [
                'label' => 'Nome da Empresa',
                'help' => 'Nome que vai ser apresentado ao cliente. Caso seja uma rede de lojas, uma sugestão é adicionar o número da loja.'
            ])
            ->add('corFundo', ColorType::class, [
                'label' => 'Cor tema',
                'help' => 'Perfil de cores que será visto pelos funcionários e clientes ao acessar as páginas dessa empresa.'
            ])
            ->add('estrategiaGeracaoCor', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    'Mono' => 'MONO',
                    'Complementar' => 'COMPLEMENTAR',
                ],
                'label' => 'Geração de paleta',
                'help' => 'Defina uma estrátegia de geração automática de paleta de cores, baseada na cor de tema.'
            ])
            ->add('corTexto', HiddenType::class)
            ->add('corInput', HiddenType::class)
            ->add('corBoxes', HiddenType::class)
            ->add('corLabel', HiddenType::class)
            ->add('arquivoLogo', VichImageType::class, [
                'required' => false,
                'help' => 'Imagem utilizada como logo nas páginas da empresa',

                'allow_delete' => false,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'asset_helper' => true
            ])
            ->add('endereco', TextType::class, [
                'label' => 'Endereço'
            ])
            ->add('cidade', TextType::class)
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
                'label' => 'CEP'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Empresa::class,
        ]);
    }
}