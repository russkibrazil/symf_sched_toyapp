<?php

namespace App\Form;

use App\Entity\Empresa;
use App\Form\EmpresaTurnoTrabalhoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class EmpresaType extends AbstractType
{
    public const EDITAR = 'EDITAR';
    public const NOVO = 'NEW';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cnpj', TextType::class, [
                'label' => 'CNPJ',
                'disabled' => $options['operacao'] == self::EDITAR ? true :false
            ])
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
            ->add('intervaloBloqueio', ChoiceType::class, [
                'choices' => [
                    'Dias' => 'DIA',
                    'Meses' => 'MES',
                    'Sem Bloqueio' => 'NUNCA'
                ],
                'label' => 'Bloquear usuários',
                'help' => 'Permite selecionar o período de bloqueio de clientes, devido a mau uso.'

            ])
            ->add('qtdeBloqueio', RangeType::class, [
                'attr' => [
                    'min' => 1,
                    'max' => 30,
                    'step' => 1
                ]
            ])
            ->add('intervaloAnalise', ChoiceType::class, [
                'choices' => [
                    'Dias' => 'DIA',
                    'Semanas' => 'SEMANA',
                    'Meses' => 'MES'
                ],
                'label' => 'Período de observação',
                'help' => 'Determina por quanto tempo o sistema mantém e contabiliza os problemas de conduta dos clientes.'
            ])
            ->add('qtdeAnalise', RangeType::class, [
                'attr' => [
                    'min' => 1,
                    'max' => 30,
                    'step' => 1
                ]
            ])
            ->add('atrasosTolerados', RangeType::class, [
                'attr' => [
                    'min' => 0,
                    'max' => 30,
                    'step' => 1
                ],
                'label' => 'Atrasos permitidos',
                'help' => 'Atrasos tolerados por cliente. Respeita o período de observação.'
            ])
            ->add('cancelamentosTolerados', RangeType::class, [
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                    'step' => 5
                ],
                'label' => 'Cancelamentos permitidos',
                'help' => 'Cancelamentos de agendamentos tolerados por cliente. Respeita o período de observação.'
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
            ->add('horarioTrabalho', CollectionType::class, [
                'entry_type' => EmpresaTurnoTrabalhoType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Empresa::class,
            'operacao' => self::EDITAR
        ]);
        $resolver->setAllowedTypes('operacao', 'string');
    }
}
