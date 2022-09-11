<?php

namespace App\Form;

use App\Entity\Servico;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ServicoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('servico', TextType::class, [
                'label' => 'Serviço',
                'help' => 'Dê um nome para o serviço.'
            ])
            ->add('descricao', TextType::class, [
                'label' => 'Descrição',
                'help' => 'Descrição breve que será mostrada para o cliente ao selecionar o serviço.'
            ])
            ->add('valor', MoneyType::class, [
                'currency' => 'BRL'
            ])
            ->add('duracao', TimeType::class, [
                'label' => 'Duração',
                'input' => 'string'
            ])
            ->add('arquivoFoto', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => true,
                'asset_helper' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Servico::class,
        ]);
    }
}
